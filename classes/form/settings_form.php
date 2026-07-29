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
 * Global Vimeo settings form for managers (mod/videoconnect:configure).
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoconnect\form;

use coding_exception;
use core\exception\moodle_exception;
use dml_exception;
use html_writer;
use mod_videoconnect\provider\vimeo_provider;
use mod_videoconnect\videoconnect;
use moodle_url;
use moodleform;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir . '/formslib.php');

/**
 * Global Vimeo settings form for managers (mod/videoconnect:configure).
 *
 * Mirrors the admin settings defined in settings.php so that users holding
 * mod/videoconnect:configure can manage them without moodle/site:config.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class settings_form extends moodleform {
    /** @var string[] Available Vimeo scopes (single source: the connector). */
    public const SCOPES = vimeo_provider::SCOPES;

    /**
     * Defines forms elements.
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function definition(): void {
        $mform = $this->_form;

        $mform->addElement('header', 'vimeoheading', get_string('vimeoheading', 'mod_videoconnect'));
        $mform->addElement('static', 'vimeoheadingdesc', '', get_string('vimeoheadingdesc', 'mod_videoconnect'));

        $mform->addElement('text', 'client_id', get_string('client_id', 'mod_videoconnect'), ['size' => 70]);
        $mform->setType('client_id', PARAM_RAW_TRIMMED);

        $mform->addElement('passwordunmask', 'client_secret', get_string('client_secret', 'mod_videoconnect'), ['size' => 70]);
        $mform->setType('client_secret', PARAM_RAW_TRIMMED);

        $mform->addElement(
            'advcheckbox',
            'is_authenticated',
            get_string('is_authenticated', 'mod_videoconnect'),
            get_string('is_authenticated_desc', 'mod_videoconnect')
        );

        $mform->addElement('passwordunmask', 'access_token', get_string('access_token', 'mod_videoconnect'), ['size' => 70]);
        $mform->setType('access_token', PARAM_RAW_TRIMMED);
        $mform->disabledIf('access_token', 'is_authenticated', 'notchecked');

        // Test de conectividad, junto a las credenciales que valida. Ojo:
        // prueba lo GUARDADO, no lo tecleado sin guardar.
        $testurl = new moodle_url('/mod/videoconnect/testconnection.php',
            ['return' => 'manage', 'sesskey' => sesskey()]);
        $mform->addElement('static', 'testconnection', '',
            html_writer::link($testurl, get_string('testconnection', 'mod_videoconnect'),
                ['class' => 'btn btn-secondary']));

        $scopesoptions = array_combine(self::SCOPES, self::SCOPES);
        $scopes = $mform->addElement(
            'select',
            'scopes',
            get_string('scopes', 'mod_videoconnect'),
            $scopesoptions
        );
        $scopes->setMultiple(true);
        $mform->addElement('static', 'scopes_desc', '', get_string('scopes_desc', 'mod_videoconnect'));

        $mform->addElement(
            'advcheckbox',
            'usewhitelist',
            get_string('usewhitelist', 'mod_videoconnect'),
            get_string('usewhitelist_desc', 'mod_videoconnect')
        );

        $mform->addElement('textarea', 'whitelist', get_string('whitelist', 'mod_videoconnect'), ['rows' => 3, 'cols' => 70]);
        $mform->setType('whitelist', PARAM_RAW_TRIMMED);
        $mform->disabledIf('whitelist', 'usewhitelist', 'notchecked');
        $mform->addElement('static', 'whitelist_desc', '', get_string('whitelist_desc', 'mod_videoconnect'));

        $mform->addElement('text', 'folderid', get_string('folderid', 'mod_videoconnect'), ['size' => 70]);
        $mform->setType('folderid', PARAM_RAW_TRIMMED);
        $mform->addElement('static', 'folderid_desc', '', get_string('folderid_desc', 'mod_videoconnect'));

        $this->add_action_buttons(false, get_string('savechanges'));
    }

    /**
     * Validates the form data.
     *
     * @param array $data
     * @param array $files
     * @return array
     * @throws coding_exception|dml_exception
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (videoconnect::extract_folderid($data['folderid'] ?? '') === null) {
            $errors['folderid'] = get_string('folderid_invalid', 'mod_videoconnect');
        }
        foreach (videoconnect::parse_domains($data['whitelist'] ?? '') as $domain) {
            if (!videoconnect::is_valid_domain($domain)) {
                $errors['whitelist'] = get_string('whitelist_invalid', 'mod_videoconnect', s($domain));
                break;
            }
        }
        return $errors;
    }
}
