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

require('../../config.php');

// Course id.
$id = required_param('id', PARAM_INT);

$course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);

require_course_login($course, true);
$PAGE->set_pagelayout('incourse');

$params = [
        'context' => context_course::instance($course->id),
];
$event = \mod_videoconnect\event\course_module_instance_list_viewed::create($params);
$event->add_record_snapshot('course', $course);
$event->trigger();

$strvideoconnect = get_string('modulename', 'videoconnect');
$strvideoconnects = get_string('modulenameplural', 'videoconnect');
$strname = get_string('name');
$strintro = get_string('moduleintro');
$strlastmodified = get_string('lastmodified');

$PAGE->set_url('/mod/videoconnect/index.php', ['id' => $course->id]);
$PAGE->set_title($course->shortname . ': ' . $strvideoconnects);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strvideoconnects);
echo $OUTPUT->header();
if (!$PAGE->has_secondary_navigation()) {
    echo $OUTPUT->heading($strvideoconnects);
}

if (!$videos = get_all_instances_in_course('videoconnect', $course)) {
    notice(get_string('thereareno', 'moodle', $strvideoconnects), "$CFG->wwwroot/course/view.php?id=$course->id");
    exit;
}

$usesections = course_format_uses_sections($course->format);

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($usesections) {
    $strsectionname = get_string('sectionname', 'format_' . $course->format);
    $table->head = [$strsectionname, $strname, $strintro];
    $table->align = ['center', 'left', 'left'];
} else {
    $table->head = [$strlastmodified, $strname, $strintro];
    $table->align = ['left', 'left', 'left'];
}

$modinfo = get_fast_modinfo($course);
$currentsection = '';
foreach ($videos as $video) {
    $cm = $modinfo->cms[$video->coursemodule];
    if ($usesections) {
        $printsection = '';
        if ($video->section !== $currentsection) {
            if ($video->section) {
                $printsection = get_section_name($course, $video->section);
            }
            if ($currentsection !== '') {
                $table->data[] = 'hr';
            }
            $currentsection = $video->section;
        }
    } else {
        $printsection = '<span class="smallinfo">' . userdate($video->timemodified) . "</span>";
    }

    $extra = empty($cm->extra) ? '' : $cm->extra;
    $icon = '';
    if (!empty($cm->icon)) {
        $icon = $OUTPUT->pix_icon($cm->icon, get_string('modulename', $cm->modname)) . ' ';
    }

    // Hidden modules are dimmed.
    $class = $video->visible ? '' : 'class="dimmed"';
    $table->data[] = [
            $printsection,
            "<a $class $extra href=\"view.php?id=$cm->id\">" . $icon . format_string($video->name) . "</a>",
            format_module_intro('videoconnect', $video, $cm->id)];
}

echo html_writer::table($table);

echo $OUTPUT->footer();
