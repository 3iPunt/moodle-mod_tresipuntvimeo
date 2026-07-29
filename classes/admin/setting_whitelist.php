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
 * Whitelist domains admin setting.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoconnect\admin;

use admin_setting_configtextarea;
use coding_exception;
use mod_videoconnect\videoconnect;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/adminlib.php');

/**
 * Whitelist domains admin setting: one domain per line, no protocol.
 * Validates every line and stores the normalised, deduped list.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class setting_whitelist extends admin_setting_configtextarea {
    /**
     * Validates and normalises the domains before saving.
     *
     * @param string $data Raw textarea value.
     * @return string Empty string on success, error message otherwise.
     * @throws coding_exception
     */
    public function write_setting($data): string {
        $domains = videoconnect::parse_domains((string) $data);
        foreach ($domains as $domain) {
            if (!videoconnect::is_valid_domain($domain)) {
                return get_string('whitelist_invalid', 'mod_videoconnect', s($domain));
            }
        }
        return parent::write_setting(implode("\n", $domains));
    }
}
