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
 * Prints an instance of mod_videoconnect.
 *
 * @package     mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_videoconnect\output\view_page;

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

global $DB, $PAGE, $OUTPUT;

// Course_module ID, or ...
$id = optional_param('id', 0, PARAM_INT);

// ... module instance id.
$a  = optional_param('a', 0, PARAM_INT);

$cmfound = false;

if ($id) {
    $cm = get_coursemodule_from_id('videoconnect', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $moduleinstance = $DB->get_record(
        'videoconnect',
        ['id' => $cm->instance],
        '*',
        MUST_EXIST
    );
    $cmfound = true;
} else if ($a) {
    $moduleinstance = $DB->get_record('videoconnect', ['id' => $a], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $moduleinstance->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance(
        'videoconnect',
        $moduleinstance->id,
        $course->id,
        false,
        MUST_EXIST
    );
    $cmfound = true;
}



if ($cmfound) {
    require_login($course, true);

    $modulecontext = context_module::instance($cm->id);

    require_capability('mod/videoconnect:view', $modulecontext);

    $event = \mod_videoconnect\event\course_module_viewed::create([
        'objectid' => $moduleinstance->id,
            'context' => $modulecontext,
    ]);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('videoconnect', $moduleinstance);
    $event->trigger();

    $PAGE->set_url('/mod/videoconnect/view.php', ['id' => $cm->id]);
    $PAGE->set_title(format_string($moduleinstance->name));
    $PAGE->set_heading(format_string($course->fullname));
    $PAGE->set_cm($cm);
    $PAGE->set_context($modulecontext);
    echo $OUTPUT->header();
    $output = $PAGE->get_renderer('mod_videoconnect');
    $page = new view_page($cm->id);
    echo $output->render($page);
} else {
    require_login();
    $context = context_system::instance();
    $PAGE->set_context($context);
    $PAGE->set_url('/mod/videoconnect/view.php', ['id' => $id]);
    echo $OUTPUT->header();
    throw new moodle_exception('missingidandcmid', 'mod_videoconnect');
}

echo $OUTPUT->footer();
