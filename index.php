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
 * Display information about all the mod_videoconnect modules in the requested course.
 *
 * @package    mod_videoconnect
 * @copyright  2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_videoconnect\event\course_module_instance_list_viewed;
use mod_videoconnect\output\index_page;

require_once(__DIR__ . '/../../config.php');

global $DB, $PAGE, $OUTPUT;

// Course id.
$id = required_param('id', PARAM_INT);

$course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);

require_course_login($course, true);
$PAGE->set_pagelayout('incourse');

$event = course_module_instance_list_viewed::create([
    'context' => context_course::instance($course->id),
]);
$event->add_record_snapshot('course', $course);
$event->trigger();

$strvideoconnects = get_string('modulenameplural', 'videoconnect');

$PAGE->set_url('/mod/videoconnect/index.php', ['id' => $course->id]);
$PAGE->set_title($course->shortname . ': ' . $strvideoconnects);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strvideoconnects);
echo $OUTPUT->header();
if (!$PAGE->has_secondary_navigation()) {
    echo $OUTPUT->heading($strvideoconnects);
}

if (!$videos = get_all_instances_in_course('videoconnect', $course)) {
    notice(
        get_string('thereareno', 'moodle', $strvideoconnects),
        new moodle_url('/course/view.php', ['id' => $course->id])
    );
    exit;
}

// El controlador resuelve las filas (sección o fecha, URL, nombre e intro
// formateados, visibilidad) y la vista solo las pinta.
$usesections = course_format_uses_sections($course->format);
$modinfo = get_fast_modinfo($course);
$currentsection = '';
$rows = [];
foreach ($videos as $video) {
    $cm = $modinfo->cms[$video->coursemodule];
    $sectionlabel = '';
    if ($usesections) {
        if ($video->section !== $currentsection) {
            if ($video->section) {
                $sectionlabel = get_section_name($course, $video->section);
            }
            $currentsection = $video->section;
        }
    } else {
        $sectionlabel = userdate($video->timemodified);
    }
    $rows[] = [
        'sectionlabel' => $sectionlabel,
        'viewurl' => (new moodle_url('/mod/videoconnect/view.php', ['id' => $cm->id]))->out(false),
        'name' => format_string($video->name, true, ['escape' => false]),
        'intro' => format_module_intro('videoconnect', $video, $cm->id),
        'dimmed' => !$video->visible,
    ];
}

$firstcolumn = $usesections
    ? get_string('sectionname', 'format_' . $course->format)
    : get_string('lastmodified');

$output = $PAGE->get_renderer('mod_videoconnect');
echo $output->render(new index_page($firstcolumn, $rows));
echo $OUTPUT->footer();
