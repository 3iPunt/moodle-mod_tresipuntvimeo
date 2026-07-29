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
 * Upload retried event.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoconnect\event;

use coding_exception;
use core\event\base;
use core\exception\moodle_exception;
use moodle_url;

/**
 * Triggered when an upload attempt is requeued from the control panel.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class upload_retried extends base {
    /**
     * Init method.
     */
    protected function init(): void {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'videoconnect_uploads';
    }

    /**
     * Returns localised general event name.
     *
     * @return string
     * @throws coding_exception
     */
    public static function get_name(): string {
        return get_string('eventuploadretried', 'mod_videoconnect');
    }

    /**
     * Returns non-localised event description with id's for admin use only.
     *
     * @return string
     */
    public function get_description(): string {
        return "The user with id '$this->userid' requeued the upload attempt with id '$this->objectid' " .
            "of the videoconnect activity with course module id '$this->contextinstanceid' from the control panel.";
    }

    /**
     * Returns relevant URL.
     *
     * @return moodle_url
     * @throws moodle_exception
     */
    public function get_url(): moodle_url {
        return new moodle_url('/mod/videoconnect/panel.php', ['instanceid' => $this->other['instanceid'] ?? 0]);
    }
}
