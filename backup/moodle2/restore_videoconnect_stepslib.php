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
 * Restore Activity Structure Step.
 *
 * @package    mod_videoconnect
 * @copyright  2024 Tresipunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Structure step to restore one videoconnect activity.
 *
 * Old backups without the provider field restore with the DB default
 * ('vimeo'), which is correct: every pre-2.1.0 video is a Vimeo video.
 *
 * @package    mod_videoconnect
 * @copyright  2024 Tresipunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_videoconnect_activity_structure_step extends restore_activity_structure_step {
    /**
     * Define Structure.
     *
     * @return mixed
     */
    protected function define_structure() {
        $paths = [];
        $paths[] = new restore_path_element('videoconnect', '/activity/videoconnect');
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process videoconnect.
     *
     * @param array $data
     * @throws base_step_exception
     * @throws dml_exception
     */
    protected function process_videoconnect($data) {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $newitemid = $DB->insert_record('videoconnect', $data);
        $this->apply_activity_instance($newitemid);
    }

    /**
     * After Execute.
     */
    protected function after_execute() {
        $this->add_related_files('mod_videoconnect', 'intro', null);
    }
}
