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
 * Folder ID admin setting.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoconnect\admin;

use admin_setting_configtext;
use mod_videoconnect\videoconnect;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/adminlib.php');

/**
 * Folder ID admin setting: accepts a numeric Vimeo folder ID or a pasted
 * folder URL and stores the normalised numeric ID.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class setting_folderid extends admin_setting_configtext {
    /**
     * Normalises the value to the numeric folder ID and saves it.
     *
     * @param string $data Raw form value (ID or folder URL).
     * @return string Empty string on success, error message otherwise.
     */
    public function write_setting($data) {
        $folderid = videoconnect::extract_folderid((string) $data);
        if ($folderid === null) {
            return get_string('folderid_invalid', 'mod_videoconnect');
        }
        return parent::write_setting($folderid);
    }
}
