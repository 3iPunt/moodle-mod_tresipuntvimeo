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
 * Global Vimeo settings page for users with mod/videoconnect:configure.
 *
 * Allows managers to configure the plugin without moodle/site:config: the
 * standard admin settings page (settings.php) is only reachable by site
 * admins because Moodle core does not load module settings.php files for
 * users without that capability.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_videoconnect\form\settings_form;

require_once(__DIR__ . '/../../config.php');

global $CFG, $PAGE, $OUTPUT;

require_login();
$context = context_system::instance();
require_capability('mod/videoconnect:configure', $context);

$url = new moodle_url('/mod/videoconnect/manage.php');
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('managesettings', 'mod_videoconnect'));
$PAGE->set_heading(get_string('managesettings', 'mod_videoconnect'));

$mform = new settings_form($url->out(false));

if ($data = $mform->get_data()) {
    set_config('client_id', trim($data->client_id ?? ''), 'mod_videoconnect');
    set_config('client_secret', trim($data->client_secret ?? ''), 'mod_videoconnect');
    set_config('is_authenticated', empty($data->is_authenticated) ? 0 : 1, 'mod_videoconnect');
    if (isset($data->access_token)) {
        // El campo va deshabilitado (disabledIf) cuando is_authenticated está
        // desmarcado y entonces no se envía: conservar el token almacenado.
        set_config('access_token', trim($data->access_token), 'mod_videoconnect');
    }
    $scopes = array_intersect((array) ($data->scopes ?? []), settings_form::SCOPES);
    set_config('scopes', implode(',', $scopes), 'mod_videoconnect');
    set_config('usewhitelist', empty($data->usewhitelist) ? 0 : 1, 'mod_videoconnect');
    if (isset($data->whitelist)) {
        // Deshabilitado (disabledIf) cuando usewhitelist está desmarcado:
        // conservar la lista almacenada. Se guarda normalizada y deduplicada.
        set_config('whitelist', implode("\n", \mod_videoconnect\videoconnect::parse_domains($data->whitelist)), 'mod_videoconnect');
    }
    // Acepta ID numérico o URL de carpeta de Vimeo; se guarda normalizado
    // (la validación de formato ya la hizo settings_form::validation()).
    set_config('folderid', \mod_videoconnect\videoconnect::extract_folderid($data->folderid ?? '') ?? '', 'mod_videoconnect');
    redirect($url, get_string('settingssaved', 'mod_videoconnect'), null, \core\output\notification::NOTIFY_SUCCESS);
}

$config = get_config('mod_videoconnect');
$mform->set_data([
    'client_id' => $config->client_id ?? '',
    'client_secret' => $config->client_secret ?? '',
    'is_authenticated' => empty($config->is_authenticated) ? 0 : 1,
    'access_token' => $config->access_token ?? '',
    'scopes' => empty($config->scopes) ? [] : explode(',', $config->scopes),
    'usewhitelist' => \mod_videoconnect\videoconnect::is_whitelist_enabled() ? 1 : 0,
    'whitelist' => $config->whitelist ?? parse_url($CFG->wwwroot, PHP_URL_HOST),
    'folderid' => $config->folderid ?? '0',
]);

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
