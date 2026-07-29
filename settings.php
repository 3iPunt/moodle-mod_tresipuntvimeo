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
 * Plugin administration pages are defined here.
 *
 * @package     mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use mod_videoconnect\admin\setting_folderid;
use mod_videoconnect\admin\setting_whitelist;
use mod_videoconnect\provider\vimeo_provider;

global $ADMIN, $CFG;

// Carpeta "Video Connect" en Extensiones > Módulos de actividad con sus
// páginas dentro, como hace mod_assign. Solo la ven administradores: el core
// no carga el settings.php de un mod sin moodle/site:config; los gestores
// con mod/videoconnect:configure acceden a manage.php por URL directa (la
// página valida la capability por sí misma).
$ADMIN->add('modsettings', new admin_category(
    'modvideoconnectfolder',
    new lang_string('pluginname', 'mod_videoconnect'),
    $module->is_enabled() === false
));

// La settingpage estándar (sección modsettingvideoconnect) pasa a colgar de
// la carpeta; al final del archivo se anula $settings para que el cargador
// del core no la añada también a Módulos de actividad.
$settings->visiblename = new lang_string('settings');
$ADMIN->add('modvideoconnectfolder', $settings);

$ADMIN->add('modvideoconnectfolder', new admin_externalpage(
    'mod_videoconnect_manage',
    new lang_string('managesettings', 'mod_videoconnect'),
    $CFG->wwwroot . '/mod/videoconnect/manage.php',
    'mod/videoconnect:configure'
));

$ADMIN->add('modvideoconnectfolder', new admin_externalpage(
    'mod_videoconnect_panel',
    new lang_string('panel', 'mod_videoconnect'),
    $CFG->wwwroot . '/mod/videoconnect/panel.php',
    'mod/videoconnect:managevideos'
));

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading(
        'mod_videoconnect/vimeosettings',
        get_string('vimeoheading', 'mod_videoconnect'),
        get_string('vimeoheadingdesc', 'mod_videoconnect')
    ));

    $settings->add(new admin_setting_configtext(
        'mod_videoconnect/client_id',
        get_string('client_id', 'mod_videoconnect'),
        '',
        '',
        PARAM_RAW,
        70
    ));

    $settings->add(new admin_setting_configpasswordunmask(
        'mod_videoconnect/client_secret',
        get_string('client_secret', 'mod_videoconnect'),
        '',
        ''
    ));

    $settings->add(new admin_setting_configcheckbox(
        'mod_videoconnect/is_authenticated',
        get_string('is_authenticated', 'mod_videoconnect'),
        get_string('is_authenticated_desc', 'mod_videoconnect'),
        false
    ));

    $settings->add(new admin_setting_configpasswordunmask(
        'mod_videoconnect/access_token',
        get_string('access_token', 'mod_videoconnect'),
        '',
        ''
    ));
    // Igual que en el formulario de gestores: el token solo aplica en modo
    // Personal Access Token.
    $settings->hide_if('mod_videoconnect/access_token', 'mod_videoconnect/is_authenticated', 'notchecked');

    // Test de conectividad, junto a las credenciales que valida
    // (credenciales + scopes concedidos por el proveedor).
    $testurl = new moodle_url('/mod/videoconnect/testconnection.php', ['return' => 'admin', 'sesskey' => sesskey()]);
    $settings->add(new admin_setting_heading(
        'mod_videoconnect/testconnection',
        '',
        html_writer::link($testurl, get_string('testconnection', 'mod_videoconnect'),
            ['class' => 'btn btn-secondary'])
    ));

    // Lista de scopes con fuente única en el conector (evita la divergencia
    // que hubo entre esta página y el formulario de gestores).
    $settings->add(new admin_setting_configmulticheckbox(
        'mod_videoconnect/scopes',
        get_string('scopes', 'mod_videoconnect'),
        get_string('scopes_desc', 'mod_videoconnect'),
        [
            'public' => 'public',
            'private' => 'private',
        ],
        array_combine(vimeo_provider::SCOPES, vimeo_provider::SCOPES)
    ));

    $settings->add(new admin_setting_configcheckbox(
        'mod_videoconnect/usewhitelist',
        get_string('usewhitelist', 'mod_videoconnect'),
        get_string('usewhitelist_desc', 'mod_videoconnect'),
        1
    ));

    $settings->add(new setting_whitelist(
        'mod_videoconnect/whitelist',
        get_string('whitelist', 'mod_videoconnect'),
        get_string('whitelist_desc', 'mod_videoconnect'),
        parse_url($CFG->wwwroot, PHP_URL_HOST)
    ));
    $settings->hide_if('mod_videoconnect/whitelist', 'mod_videoconnect/usewhitelist', 'notchecked');

    $settings->add(new setting_folderid(
        'mod_videoconnect/folderid',
        get_string('folderid', 'mod_videoconnect'),
        get_string('folderid_desc', 'mod_videoconnect'),
        0
    ));
}

// Ya añadida dentro de la carpeta modvideoconnectfolder: evitar que el
// cargador del core la añada de nuevo directamente a Módulos de actividad.
$settings = null;
