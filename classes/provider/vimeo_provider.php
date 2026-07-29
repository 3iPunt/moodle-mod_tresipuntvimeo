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
 * Vimeo provider connector.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoconnect\provider;

use coding_exception;
use dml_exception;
use mod_videoconnect\response;
use mod_videoconnect\vimeo;
use moodle_exception;
use Throwable;

/**
 * Vimeo connector: wraps the low-level API client (classes/vimeo.php) and
 * owns every Vimeo-specific detail — reference/URL formats, privacy policy
 * of the uploads and the plugin settings that configure the account
 * (client_id, client_secret, access_token, scopes: legacy unprefixed keys
 * owned by this connector; future connectors prefix their own).
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class vimeo_provider implements provider_interface {
    /** @var string[] Every scope offered by the Vimeo API (single source of truth for the settings UIs). */
    public const SCOPES = [
        'public',
        'private',
        'purchased',
        'create',
        'edit',
        'delete',
        'interact',
        'upload',
        'promo_codes',
        'video_files',
    ];

    /** @var string[] Scopes required by the full plugin flow (upload + whitelist + folder). */
    public const REQUIRED_SCOPES = ['public', 'private', 'upload', 'edit', 'interact'];

    /** @var vimeo|null Lazy API client, created on connect(). */
    protected ?vimeo $client = null;

    /**
     * Machine name of the provider.
     *
     * @return string
     */
    public function get_name(): string {
        return 'vimeo';
    }

    /**
     * Human name of the provider.
     *
     * @return string
     */
    public function get_display_name(): string {
        return 'Vimeo';
    }

    /**
     * Pix key of the provider logo.
     *
     * @return string
     */
    public function get_logo_pix(): string {
        return 'icon';
    }

    /**
     * Extracts the numeric Vimeo video ID from a raw value.
     *
     * Accepts a plain numeric ID or a Vimeo video URL (vimeo.com/<id>,
     * player.vimeo.com/video/<id>, vimeo.com/manage/videos/<id>, ...).
     * Unlisted videos with a privacy hash are not supported: the module
     * relies on domain-whitelist embed privacy, not on hashes.
     *
     * @param string $value Raw user input.
     * @return string|null Numeric ID, '' when empty, or null when invalid.
     */
    public function parse_video_reference(string $value): ?string {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        if (preg_match('~^\d+$~', $value)) {
            return $value;
        }
        if (preg_match('~^(?:https?://)?(?:www\.)?(?:player\.)?vimeo\.com/(?:[a-z]+/)*(?:video/)?(\d+)~i', $value, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Extracts the numeric Vimeo folder ID from a raw value.
     *
     * Accepts a plain numeric ID or a Vimeo folder URL, e.g.:
     * - https://vimeo.com/manage/folders/<id>
     * - https://vimeo.com/user/<userid>/folder/<id>?isPrivate=true
     *
     * @param string $value Raw user input.
     * @return string|null Numeric ID, '' when empty, or null when invalid.
     */
    public function parse_folder_reference(string $value): ?string {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        if (preg_match('~^\d+$~', $value)) {
            return $value;
        }
        if (preg_match('~vimeo\.com/.*folders?/(\d+)~i', $value, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Embed player URL of a video.
     *
     * @param string $videoid
     * @return string
     */
    public function get_embed_url(string $videoid): string {
        return 'https://player.vimeo.com/video/' . $videoid
            . '?badge=0&autopause=0&player_id=0&app_id=58479';
    }

    /**
     * Public page URL of a video on Vimeo.
     *
     * @param string $videoid
     * @return string
     */
    public function get_video_url(string $videoid): string {
        return 'https://vimeo.com/' . $videoid;
    }

    /**
     * Initialises the Vimeo API client.
     *
     */
    public function connect(): void {
        if ($this->client === null) {
            $this->client = new vimeo();
        }
    }

    /**
     * Uploads a local file applying the Vimeo privacy policy of the plugin:
     * never listed on vimeo.com, embed restricted (or public), no comments
     * and no downloads.
     *
     * @param string $filepath Local path of the temp file.
     * @param string $name Video title.
     * @param bool $restrictembed Whether embedding is whitelist-restricted.
     * @return response
     * @throws coding_exception
     */
    public function upload(string $filepath, string $name, bool $restrictembed): response {
        return $this->client()->upload($filepath, [
            'name' => $name,
            'privacy' => [
                'view' => 'disable',
                'embed' => $restrictembed ? 'whitelist' : 'public',
                'comments' => 'nobody',
                'download' => false,
            ],
        ]);
    }

    /**
     * Extracts the numeric video ID from the URI returned by an upload.
     *
     * @param string $uploadresult URI such as /videos/123456789.
     * @return int Video ID, 0 when it cannot be extracted.
     */
    public function extract_videoid(string $uploadresult): int {
        $last = strrpos($uploadresult, '/');
        return $last !== false ? intval(substr($uploadresult, $last + 1)) : intval($uploadresult);
    }

    /**
     * Allows one domain to embed the video.
     *
     * @param int $videoid
     * @param string $domain Domain without protocol.
     * @return response
     * @throws coding_exception
     */
    public function add_domain_whitelist(int $videoid, string $domain): response {
        return $this->client()->add_domain_whitelist($videoid, $domain);
    }

    /**
     * Moves the video to a folder of the Vimeo account.
     *
     * @param int $videoid
     * @param string $folderid
     * @return response
     * @throws coding_exception
     */
    public function add_video_to_folder(int $videoid, string $folderid): response {
        return $this->client()->add_video_to_folder($videoid, (int) $folderid);
    }

    /**
     * Tests the connection against the Vimeo API.
     *
     * Validates the credentials (GET /oauth/verify) and compares the granted
     * scopes with the ones the full flow needs, reporting the missing ones.
     *
     * @return array success (bool), level ('success'|'warning'|'error') and message.
     * @throws coding_exception
     */
    public function check_connection(): array {
        try {
            $this->connect();
        } catch (Throwable $e) {
            return [
                'success' => false,
                'level' => 'error',
                'message' => get_string('testconnection_failed', 'mod_videoconnect', $e->getMessage()),
            ];
        }

        $response = $this->client()->verify();
        if (!$response->success) {
            return [
                'success' => false,
                'level' => 'error',
                'message' => get_string('testconnection_failed', 'mod_videoconnect', $response->error->message),
            ];
        }

        $body = json_decode($response->data, true) ?: [];
        $identity = $body['app']['name'] ?? '';
        if (!empty($body['user']['name'])) {
            $identity .= ($identity !== '' ? ' · ' : '') . $body['user']['name'];
        }
        $granted = preg_split('/\s+/', (string) ($body['scope'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
        $missing = array_diff(self::REQUIRED_SCOPES, $granted);

        if (!empty($missing)) {
            return [
                'success' => true,
                'level' => 'warning',
                'message' => get_string('testconnection_missingscopes', 'mod_videoconnect', (object) [
                    'identity' => $identity,
                    'missing' => implode(', ', $missing),
                ]),
            ];
        }
        return [
            'success' => true,
            'level' => 'success',
            'message' => get_string('testconnection_ok', 'mod_videoconnect', $identity),
        ];
    }

    /**
     * API client, guarding against use before connect().
     *
     * @return vimeo
     * @throws coding_exception
     */
    protected function client(): vimeo {
        if ($this->client === null) {
            throw new coding_exception('vimeo_provider used before connect()');
        }
        return $this->client;
    }
}
