<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Class Vimeo
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoconnect;

use curl;
use dml_exception;
use Exception;
use moodle_exception;
use moodle_url;
use Vimeo\Exceptions\VimeoRequestException;
use Vimeo\Exceptions\VimeoUploadException;
use Vimeo\Vimeo as vimeoclient;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/lib/filelib.php');
require_once($CFG->dirroot . '/mod/videoconnect/.extlib/vendor/autoload.php');

/**
 * Class Vimeo
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class vimeo {
    /** @var int Timeout */
    public const TIMEOUT = 30;

    /** @var string|false Client ID (false when not configured). */
    protected mixed $clientid;

    /** @var string|false Client Secret (false when not configured). */
    protected mixed $clientsecret;

    /** @var bool|string|false Whether a Personal Access Token is used. */
    protected mixed $isauthenticated;

    /** @var string[] Scopes */
    protected array $scopes = ['public', 'private', 'upload'];

    /** @var vimeoclient Vimeo API client. */
    protected vimeoclient $vimeo;

    /** @var string Access Token */
    protected string $accesstoken;

    /**
     * vimeo constructor.
     *
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function __construct() {
        $this->clientid = get_config('mod_videoconnect', 'client_id');
        $this->clientsecret = get_config('mod_videoconnect', 'client_secret');
        $this->isauthenticated = get_config('mod_videoconnect', 'is_authenticated');
        $this->set_scopes();
        $this->init_vimeo();
    }

    /**
     * Set scopes.
     *
     * @throws dml_exception
     * @throws moodle_exception
     */
    protected function set_scopes(): void {
        $scopes = get_config('mod_videoconnect', 'scopes');
        if (!empty($scopes)) {
            $this->scopes = explode(',', $scopes);
        } else {
            $moodleurl = new moodle_url('/admin/settings.php', ['section' => 'modsettingvideoconnect']);
            throw new moodle_exception('scopes_not_exist', 'mod_videoconnect', $moodleurl->out(false));
        }
    }

    /**
     * Init Vimeo
     *
     * @throws dml_exception
     * @throws moodle_exception
     */
    protected function init_vimeo(): void {
        if ($this->isauthenticated) {
            $token = get_config('mod_videoconnect', 'access_token');
            if (empty($token) || !is_string($token)) {
                // Sin esta guarda, el false de get_config cuando no existe el
                // token era un TypeError fatal al asignarse a string.
                throw new moodle_exception('accesstoken_missing', 'mod_videoconnect');
            }
            $this->accesstoken = $token;
            $this->vimeo = new vimeoclient($this->clientid, $this->clientsecret, $this->accesstoken);
        } else {
            $this->vimeo = new vimeoclient($this->clientid, $this->clientsecret);
            $token = $this->vimeo->clientCredentials($this->scopes);
            if (isset($token['body']['access_token'])) {
                $this->accesstoken = $token['body']['access_token'];
                $this->vimeo->setToken($this->accesstoken);
            } else {
                // Respuesta sin token: componer el detalle con lo que haya
                // (el cuerpo puede no traer error_code/error).
                $detail = ($token['body']['error_code'] ?? '?') . ': '
                    . ($token['body']['error'] ?? json_encode($token['body'] ?? null));
                throw new moodle_exception('clientcredentials_failed', 'mod_videoconnect', '', $detail);
            }
        }
    }

    /**
     * Upload Video to Vimeo.
     *
     * @param string $filepath
     * @param array $params https://developer.vimeo.com/api/reference/videos#upload_video
     * @return response
     */
    public function upload(string $filepath, array $params): response {
        try {
            $response = $this->vimeo->upload($filepath, $params);
            return new response(true, $response, new error(0, ''));
        } catch (VimeoRequestException $e) {
            return new response(
                    false,
                    '',
                    new error(3001, $e->getMessage())
            );
        } catch (VimeoUploadException $e) {
            return new response(
                    false,
                    '',
                    new error(3000, $e->getMessage())
            );
        }
    }

    /**
     * Add Domain whitelist
     *
     * @param int $videoid
     * @param string $domain
     * @return response
     */
    public function add_domain_whitelist(int $videoid, string $domain): response {
        $url = 'https://api.vimeo.com/videos/' . $videoid . '/privacy/domains/' . $domain;
        $params = [];
        return $this->curl_request($url, $params);
    }

    /**
     * Add Video to Folder
     *
     * @param int $videoid
     * @param int $folderid
     * @return response
     */
    public function add_video_to_folder(int $videoid, int $folderid): response {
        $url = 'https://api.vimeo.com/me/projects/' . $folderid . '/videos/' . $videoid;
        $params = [];
        return $this->curl_request($url, $params);
    }

    /**
     * Verifies the access token against the Vimeo API.
     *
     * GET /oauth/verify validates the token and returns its metadata
     * (application, user and granted scopes) — response::$data carries the
     * raw JSON body on success.
     *
     * @return response
     */
    public function verify(): response {
        try {
            $curl = new curl();
            $curl->setHeader([
                'Content-type: application/json',
                'Authorization: Bearer ' . $this->accesstoken,
            ]);
            $result = $curl->get('https://api.vimeo.com/oauth/verify', [], $this->get_options_curl());
            $info = $curl->get_info();
            $httpcode = (int) ($info['http_code'] ?? 0);
            if ($httpcode >= 200 && $httpcode < 300) {
                return new response(true, (string) $result, new error(0, ''));
            }
            $decoded = json_decode((string) $result, true);
            $message = $decoded['developer_message']
                ?? $decoded['error']
                ?? ('HTTP ' . $httpcode . ': ' . substr((string) $result, 0, 200));
            return new response(false, '', new error(4004, $message));
        } catch (Exception $e) {
            return new response(false, '', new error(4001, $e->getMessage()));
        }
    }

    /**
     * CURL Request.
     *
     * @param string $url
     * @param array $params
     * @return response
     */
    private function curl_request(string $url, array $params = []): response {
        try {
            $curl = new curl();
            $headers = [];
            $headers[] = 'Content-type: application/json';
            $headers[] = 'Authorization: Bearer ' . $this->accesstoken;
            $curl->setHeader($headers);
            $result = $curl->put($url, json_encode($params, JSON_THROW_ON_ERROR), $this->get_options_curl());

            // Vimeo responde 204 No Content (cuerpo vacío) en estos PUT: el
            // éxito se decide por el código HTTP, no intentando decodificar
            // un body que puede no existir.
            $info = $curl->get_info();
            $httpcode = (int) ($info['http_code'] ?? 0);
            if ($httpcode >= 200 && $httpcode < 300) {
                return new response(true, '', new error(0, ''));
            }

            // Mensaje lo más claro posible: developer_message de Vimeo si
            // existe, si no su error, y como último recurso código + body.
            $decoded = json_decode((string) $result, true);
            $message = $decoded['developer_message']
                ?? $decoded['error']
                ?? ('HTTP ' . $httpcode . ': ' . substr((string) $result, 0, 200));
            return new response(
                    false,
                    '',
                    new error(4002, $message)
            );
        } catch (Exception $e) {
            return new response(
                    false,
                    '',
                    new error(4001, $e->getMessage())
            );
        }
    }

    /**
     * Get Options CURL.
     *
     * @return array
     */
    private function get_options_curl(): array {
        return [
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_TIMEOUT' => self::TIMEOUT,
                'CURLOPT_HTTP_VERSION' => CURL_HTTP_VERSION_1_1,
                'CURLOPT_SSLVERSION' => CURL_SSLVERSION_TLSv1_2,
        ];
    }
}
