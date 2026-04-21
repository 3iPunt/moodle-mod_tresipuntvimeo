<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Library of interface functions and constants.
 *
 * @package     mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

use mod_videoconnect\output\view_page;
use mod_videoconnect\uploads;

defined('MOODLE_INTERNAL') || die();
/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return int True if the feature is supported, null otherwise.
 */
function videoconnect_supports(string $feature) {
    switch ($feature) {
        case FEATURE_BACKUP_MOODLE2:
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_RESOURCE;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_videoconnect into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param null $mform The form.
 * @return int The id of the newly inserted record.
 * @throws dml_exception
 */
function videoconnect_add_instance(object $moduleinstance, $mform = null): int {
    global $DB;
    $moduleinstance->timecreated = time();
    $id = $DB->insert_record('videoconnect', $moduleinstance);
    $moduleinstance->instance = $id;
    uploads::update($moduleinstance, $mform);
    return $id;
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 * See get_array_of_activities() in course/lib.php
 *
 * @param object $coursemodule
 * @return cached_cm_info|null
 * @throws coding_exception
 * @throws moodle_exception
 */
function videoconnect_get_coursemodule_info(object $coursemodule): cached_cm_info {
    global $PAGE;
    $output = $PAGE->get_renderer('mod_videoconnect');
    $page = new view_page($coursemodule->id, false);
    $content = $output->render($page);
    $info = new cached_cm_info();
    $info->content = $content;
    return $info;
}

/**
 * Updates an instance of the mod_videoconnect in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance
 * @param mod_videoconnect_mod_form|null $mform
 * @return bool
 * @throws dml_exception
 * @throws moodle_exception
 */
function videoconnect_update_instance(object $moduleinstance, mod_videoconnect_mod_form $mform): bool {
    global $DB;
    $moduleinstance = uploads::update($moduleinstance, $mform);
    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;
    return $DB->update_record('videoconnect', $moduleinstance);
}

/**
 * Removes an instance of the mod_videoconnect from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 * @throws dml_exception
 */
function videoconnect_delete_instance(int $id): bool {
    global $DB;

    if (!$DB->record_exists('videoconnect', ['id' => $id])) {
        return false;
    }

    $DB->delete_records('videoconnect_uploads', [
        'instance' => $id,
    ]);

    $DB->delete_records('videoconnect', ['id' => $id]);
    return true;
}
