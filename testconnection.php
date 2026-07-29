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
 * Provider connectivity test endpoint.
 *
 * Connects to the active video provider, validates the credentials and the
 * granted scopes, and redirects back to the settings with the verdict as a
 * notification. Guarded by mod/videoconnect:configure (same capability as
 * the settings it validates).
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\notification;
use mod_videoconnect\provider\provider_manager;

require_once(__DIR__ . '/../../config.php');

global $PAGE;

require_login();
$context = context_system::instance();
require_capability('mod/videoconnect:configure', $context);
require_sesskey();

$return = optional_param('return', 'manage', PARAM_ALPHA);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/mod/videoconnect/testconnection.php'));

$returnurl = $return === 'admin'
    ? new moodle_url('/admin/settings.php', ['section' => 'modsettingvideoconnect'])
    : new moodle_url('/mod/videoconnect/manage.php');

$result = provider_manager::get_active()->check_connection();

$levels = [
    'success' => notification::NOTIFY_SUCCESS,
    'warning' => notification::NOTIFY_WARNING,
    'error' => notification::NOTIFY_ERROR,
];
redirect($returnurl, $result['message'], null, $levels[$result['level']] ?? notification::NOTIFY_INFO);
