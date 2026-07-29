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
 * Backup Activity Structure Step.
 *
 * @package    mod_videoconnect
 * @copyright  2024 Tresipunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define the complete videoconnect structure for backup, with file and id annotations.
 *
 * The upload queue ({videoconnect_uploads}) is NOT included on purpose: its
 * rows reference server-local temp file paths that do not exist on the
 * restore target, so restoring them would only produce dead queue entries.
 * A restored activity keeps its published video (idvideo + provider) or
 * starts clean.
 *
 * @package    mod_videoconnect
 * @copyright  2024 Tresipunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_videoconnect_activity_structure_step extends backup_activity_structure_step {
    /**
     * Define Structure.
     *
     * @return backup_nested_element
     * @throws base_element_struct_exception
     */
    protected function define_structure(): backup_nested_element {

        // Define each element separated.
        $videoconnect = new backup_nested_element('videoconnect', ['id'], [
            'course', 'name', 'idvideo', 'provider', 'displayinline', 'intro', 'introformat',
            'timecreated', 'timemodified']);

        // Define sources.
        $videoconnect->set_source_table('videoconnect', ['id' => backup::VAR_ACTIVITYID]);

        // Define id annotations.
        // (none).

        // Define file annotations.
        $videoconnect->annotate_files('mod_videoconnect', 'intro', null);
        // This file area hasn't itemid.

        // Return the root element (videoconnect), wrapped into standard activity structure.
        return $this->prepare_activity_structure($videoconnect);
    }
}
