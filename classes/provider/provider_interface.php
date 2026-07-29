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
 * Video provider contract.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoconnect\provider;

use mod_videoconnect\response;
use moodle_exception;

/**
 * Contract every video provider connector implements.
 *
 * The plugin core (upload task, forms, views) works exclusively against
 * this interface: adding a new provider means adding a connector class and
 * registering it in provider_manager, without touching the core (closed
 * list by design decision 2026-07-28 — no subplugin machinery until there
 * is real demand for third-party connectors).
 *
 * Reference/URL methods must be cheap and offline; only connect() and the
 * operations that follow it may talk to the provider API.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface provider_interface {
    /**
     * Machine name of the provider (stored in {videoconnect}.provider).
     *
     * @return string
     */
    public function get_name(): string;

    /**
     * Human name of the provider, shown in the UI.
     *
     * @return string
     */
    public function get_display_name(): string;

    /**
     * Pix key (within this plugin) of the provider logo.
     *
     * @return string
     */
    public function get_logo_pix(): string;

    /**
     * Extracts the video ID from raw user input (plain ID or pasted URL).
     *
     * @param string $value Raw user input.
     * @return string|null Video ID, '' when empty, or null when invalid.
     */
    public function parse_video_reference(string $value): ?string;

    /**
     * Extracts the folder ID from raw user input (plain ID or pasted URL).
     *
     * @param string $value Raw user input.
     * @return string|null Folder ID, '' when empty, or null when invalid.
     */
    public function parse_folder_reference(string $value): ?string;

    /**
     * Embed player URL of a video (offline; no API call).
     *
     * @param string $videoid
     * @return string
     */
    public function get_embed_url(string $videoid): string;

    /**
     * Public page URL of a video on the provider (offline; no API call).
     *
     * @param string $videoid
     * @return string
     */
    public function get_video_url(string $videoid): string;

    /**
     * Initialises the API client (credentials/token). Must be called before
     * any operation below; cheap methods above never require it.
     *
     * @throws moodle_exception When the configuration is missing or the
     *         provider rejects the credentials.
     */
    public function connect(): void;

    /**
     * Tests the connection with the provider (settings "Test connection").
     *
     * Connects, validates the credentials against the provider API and
     * checks that the granted permissions cover the full plugin flow.
     * Never throws: failures are reported in the result.
     *
     * @return array success (bool), level ('success'|'warning'|'error')
     *         and message (string, localised, ready to notify).
     */
    public function check_connection(): array;

    /**
     * Uploads a local file, applying the provider privacy policy.
     *
     * @param string $filepath Local path of the temp file.
     * @param string $name Video title.
     * @param bool $restrictembed Whether embedding is restricted to the
     *        whitelisted domains (false = public embed).
     * @return response
     */
    public function upload(string $filepath, string $name, bool $restrictembed): response;

    /**
     * Extracts the numeric video ID from a successful upload result.
     *
     * @param string $uploadresult response::$data of a successful upload.
     * @return int Video ID, 0 when it cannot be extracted.
     */
    public function extract_videoid(string $uploadresult): int;

    /**
     * Allows one domain to embed the video.
     *
     * @param int $videoid
     * @param string $domain Domain without protocol.
     * @return response
     */
    public function add_domain_whitelist(int $videoid, string $domain): response;

    /**
     * Moves the video to a folder of the provider account.
     *
     * @param int $videoid
     * @param string $folderid
     * @return response
     */
    public function add_video_to_folder(int $videoid, string $folderid): response;
}
