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

    /** @var string Client ID */
    protected mixed $clientid;

    /** @var string Client Secret */
    protected mixed $clientsecret;

    /** @var string Is authenticated? */
    protected mixed $isauthenticated;

    /** @var string[] Scopes */
    protected array $scopes = ['public', 'private', 'upload'];

    /** @var \Vimeo\Vimeo Vimeo */
    protected \Vimeo\Vimeo $vimeo;

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
            $moodleurl = new moodle_url('/admin/settings.php?section=modsettingvideoconnect');
            throw new moodle_exception(
                    get_string('scopes_not_exist', 'mod_videoconnect') .
                    '. ' . $moodleurl->out(false)
            );
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
            $this->accesstoken = get_config('mod_videoconnect', 'access_token');
            $this->vimeo = new \Vimeo\Vimeo($this->clientid, $this->clientsecret, $this->accesstoken);
        } else {
            $this->vimeo = new \Vimeo\Vimeo($this->clientid, $this->clientsecret);
            $token = $this->vimeo->clientCredentials($this->scopes);
            if (isset($token['body']['access_token'])) {
                $this->accesstoken = $token['body']['access_token'];
                $this->vimeo->setToken($this->accesstoken);
            } else {
                throw new moodle_exception($token["body"]["error_code"] . ': ' . $token["body"]["error"]);
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
            $result = json_decode($result, true, 512, JSON_THROW_ON_ERROR);
            if ($result['error']) {
                return new response(
                        false,
                        '',
                        new error(4002, $result['error'])
                );
            }
            return new response(true, '', new error(0, ''));
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
