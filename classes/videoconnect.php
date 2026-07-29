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
 * Plugin helper.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoconnect;

use core_text;
use mod_videoconnect\provider\provider_manager;
use stdClass;

/**
 * Plugin helper: static utility methods.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class videoconnect {
    /**
     * All videoconnect instances of a course, cached per request.
     *
     * The course page renders every instance inline (cm_info_view): without
     * this cache each activity fired its own get_record on every course
     * page load.
     *
     * @param int $courseid
     * @return stdClass[] Instance records keyed by id.
     * @throws \dml_exception
     */
    public static function get_course_instances(int $courseid): array {
        global $DB;
        static $cache = [];
        if (!isset($cache[$courseid])) {
            $cache[$courseid] = $DB->get_records('videoconnect', ['course' => $courseid]);
        }
        return $cache[$courseid];
    }

    /**
     * Whether uploaded videos must be restricted to whitelisted domains.
     *
     * Installs older than the usewhitelist setting keep the historical
     * behaviour (whitelist always on) until an admin saves the settings.
     *
     * @return bool
     * @throws \dml_exception
     */
    public static function is_whitelist_enabled(): bool {
        $value = get_config('mod_videoconnect', 'usewhitelist');
        return $value === false ? true : (bool) $value;
    }

    /**
     * Clean list of configured whitelist domains.
     *
     * @return string[]
     * @throws \dml_exception
     */
    public static function get_whitelist_domains(): array {
        return self::parse_domains((string) get_config('mod_videoconnect', 'whitelist'));
    }

    /**
     * Parses a raw domains value (one per line) into a clean, deduped list.
     *
     * @param string $raw
     * @return string[]
     */
    public static function parse_domains(string $raw): array {
        $domains = [];
        foreach (preg_split('/\R+/', $raw) ?: [] as $line) {
            $line = core_text::strtolower(trim($line));
            if ($line !== '') {
                $domains[$line] = $line;
            }
        }
        return array_values($domains);
    }

    /**
     * Whether a value is a plausible domain (no protocol, no path).
     *
     * @param string $domain
     * @return bool
     */
    public static function is_valid_domain(string $domain): bool {
        return (bool) preg_match('/^[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)+$/i', $domain);
    }

    /**
     * Extracts the folder ID from a raw value (plain ID or pasted URL).
     *
     * Delegates to the active provider connector, which knows its own
     * folder URL formats.
     *
     * @param string $value Raw user input.
     * @return string|null Numeric ID, '' when empty, or null when invalid.
     * @throws \dml_exception
     */
    public static function extract_folderid(string $value): ?string {
        return provider_manager::get_active()->parse_folder_reference($value);
    }
}
