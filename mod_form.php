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
 * The main mod_videoconnect configuration form.
 *
 * @package     mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use mod_videoconnect\provider\provider_manager;

require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_videoconnect_mod_form extends moodleform_mod {
    /**
     * Defines forms elements
     * @throws coding_exception
     */
    public function definition() {
        global $CFG;

        $course = $this->_course;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement(
            'text',
            'name',
            get_string('videoconnectname', 'mod_videoconnect'),
            ['size' => '64']
        );

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule(
            'name',
            get_string('maximumchars', '', 255),
            'maxlength',
            255,
            'client'
        );
        $mform->addHelpButton('name', 'videoconnectname', 'mod_videoconnect');

        // Adding the standard "intro" and "introformat" fields.
        $this->standard_intro_elements();

        $mform->addElement(
            'text',
            'idvideo',
            get_string('idvideo', 'mod_videoconnect')
        );

        $mform->addRule(
            'idvideo',
            get_string('maximumchars', '', 255),
            'maxlength',
            255,
            'client'
        );

        // Raw on purpose: accepts a numeric ID or a pasted Vimeo URL; it is
        // validated in validation() and normalised to the numeric ID in
        // data_postprocessing().
        $mform->setType('idvideo', PARAM_RAW_TRIMMED);
        $mform->addHelpButton('idvideo', 'idvideo', 'mod_videoconnect');

        $filemanageroptions['accepted_types'] = ['.mp4', '.mov', '.wmv', '.avi', '.flv'];
        $filemanageroptions['maxbytes'] = $course->maxbytes;
        $filemanageroptions['maxfiles'] = 1;
        $filemanageroptions['mainfile'] = true;

        $mform->addElement(
            'filepicker',
            'filevimeo',
            get_string('selectvideo', 'mod_videoconnect'),
            null,
            $filemanageroptions
        );

        // Embebido en la página del curso (histórico, default) o solo
        // dentro de la actividad.
        $mform->addElement(
            'advcheckbox',
            'displayinline',
            get_string('displayinline', 'mod_videoconnect')
        );
        $mform->addHelpButton('displayinline', 'displayinline', 'mod_videoconnect');
        $mform->setDefault('displayinline', 1);

        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }

    /**
     * Validates the form data.
     *
     * The video reference (plain ID or pasted URL) is parsed by the active
     * provider connector: each provider knows its own URL formats.
     *
     * @param array $data
     * @param array $files
     * @return array
     * @throws coding_exception
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (!empty($data['idvideo'])
                && provider_manager::get_active()->parse_video_reference($data['idvideo']) === null) {
            $errors['idvideo'] = get_string('idvideo_invalid', 'mod_videoconnect');
        }
        return $errors;
    }

    /**
     * Normalises idvideo to the plain numeric ID before it is saved.
     *
     * @param stdClass $data Form data.
     */
    public function data_postprocessing($data) {
        parent::data_postprocessing($data);
        if (isset($data->idvideo)) {
            $data->idvideo = (string) (provider_manager::get_active()->parse_video_reference($data->idvideo) ?? '');
        }
    }
}
