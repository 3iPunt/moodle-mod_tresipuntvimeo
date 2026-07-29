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
 * Video Connect control panel.
 *
 * Lists every Video Connect activity of the site with its derived video
 * state, the upload attempts detail, and management actions (retry /
 * discard) over the upload queue.
 *
 * Guarded by mod/videoconnect:managevideos via require_capability (not
 * admin_externalpage_setup): managers without moodle/site:config do not
 * have the page in their admin tree but must be able to access by URL.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\notification;
use core\task\manager as task_manager;
use mod_videoconnect\output\detail_page;
use mod_videoconnect\output\panel_page;
use mod_videoconnect\provider\provider_manager;
use mod_videoconnect\table\videos_table;
use mod_videoconnect\task\upload_videos_task;
use mod_videoconnect\uploads;

require_once(__DIR__ . '/../../config.php');

global $CFG, $DB, $PAGE, $OUTPUT;

require_login();
$context = context_system::instance();
require_capability('mod/videoconnect:managevideos', $context);

$instanceid = optional_param('instanceid', 0, PARAM_INT);

// Acciones de gestión (reintentar / descartar) con confirmación + sesskey.
$action = optional_param('action', '', PARAM_ALPHA);
$uploadid = optional_param('uploadid', 0, PARAM_INT);
if ($action !== '' && $uploadid > 0 && in_array($action, ['retry', 'discard'], true)) {
    require_sesskey();
    $detailurl = new moodle_url('/mod/videoconnect/panel.php', ['instanceid' => $instanceid]);
    if (!optional_param('confirm', 0, PARAM_BOOL)) {
        // Paso 1: confirmación.
        $PAGE->set_context($context);
        $PAGE->set_url(new moodle_url('/mod/videoconnect/panel.php', [
            'instanceid' => $instanceid, 'action' => $action, 'uploadid' => $uploadid,
        ]));
        $PAGE->set_pagelayout('admin');
        $PAGE->set_title(get_string('panel', 'mod_videoconnect'));
        $PAGE->set_heading(get_string('panel', 'mod_videoconnect'));
        $continueurl = new moodle_url('/mod/videoconnect/panel.php', [
            'instanceid' => $instanceid, 'action' => $action, 'uploadid' => $uploadid,
            'confirm' => 1, 'sesskey' => sesskey(),
        ]);
        echo $OUTPUT->header();
        echo $OUTPUT->confirm(get_string('confirm' . $action, 'mod_videoconnect'), $continueurl, $detailurl);
        echo $OUTPUT->footer();
        exit;
    }
    // Paso 2: ejecutar (las salvaguardas viven en el modelo). Si la acción
    // no es posible (carrera con el cron, fichero purgado, vídeo ya
    // publicado...), el usuario vuelve al detalle con la causa exacta en
    // una notificación — nunca a una página de error.
    try {
        if ($action === 'retry') {
            uploads::retry($uploadid);
            redirect($detailurl, get_string('retrydone', 'mod_videoconnect'), null, notification::NOTIFY_SUCCESS);
        } else {
            uploads::discard($uploadid);
            redirect($detailurl, get_string('discarddone', 'mod_videoconnect'), null, notification::NOTIFY_SUCCESS);
        }
    } catch (moodle_exception $e) {
        redirect($detailurl, $e->getMessage(), null, notification::NOTIFY_ERROR);
    }
}

// URL de ajustes para quien puede configurar (manage.php valida su propia
// capability; misma fuente de verdad que la settingpage de administración).
$settingsurl = has_capability('mod/videoconnect:configure', $context)
    ? new moodle_url('/mod/videoconnect/manage.php')
    : null;

// Conector activo: marca de la cabecera y URLs hacia el proveedor. Con un
// único conector cubre también las filas históricas; cuando exista un
// segundo, listado y detalle resolverán por el sello de cada instancia.
$activeprovider = provider_manager::get_active();

// Vista de detalle de una actividad (intentos de subida).
if ($instanceid > 0) {
    $activity = uploads::get_panel_row($instanceid);
    $attempts = uploads::get_attempts($instanceid);
    $lastlive = null;
    foreach ($attempts as $attempt) {
        // I/O y reglas de negocio resueltas en el controlador/modelo: la
        // vista solo recibe flags y URLs ya construidas.
        $attempt->fileavailable = !empty($attempt->filepath) && file_exists($attempt->filepath);
        $attempt->canretry = uploads::is_retryable($attempt, $activity);
        $attempt->candiscard = in_array((int) $attempt->status, uploads::DISCARDABLE_STATUSES, true);
        $attempt->notrecoverable = !$attempt->canretry
            && empty($activity->idvideo)
            && in_array((int) $attempt->status, uploads::RETRYABLE_STATUSES, true);
        $actionparams = ['instanceid' => $instanceid, 'uploadid' => (int) $attempt->id, 'sesskey' => sesskey()];
        $attempt->retryurl = new moodle_url('/mod/videoconnect/panel.php', $actionparams + ['action' => 'retry']);
        $attempt->discardurl = new moodle_url('/mod/videoconnect/panel.php', $actionparams + ['action' => 'discard']);
        // El último intento no descartado (los intentos vienen del más
        // reciente al más antiguo) alimenta la tarjeta de diagnóstico.
        if ($lastlive === null && (int) $attempt->status !== uploads::STATUS_DISCARDED) {
            $lastlive = $attempt;
        }
    }

    $url = new moodle_url('/mod/videoconnect/panel.php', ['instanceid' => $instanceid]);
    $PAGE->set_context($context);
    $PAGE->set_url($url);
    $PAGE->set_pagelayout('admin');
    $PAGE->set_title(get_string('detail_title', 'mod_videoconnect'));
    $PAGE->set_heading(get_string('detail_title', 'mod_videoconnect'));

    $output = $PAGE->get_renderer('mod_videoconnect');
    echo $OUTPUT->header();
    echo $output->render(new detail_page(
        $activity,
        $attempts,
        new moodle_url('/mod/videoconnect/panel.php'),
        $lastlive,
        $activeprovider
    ));
    echo $OUTPUT->footer();
    exit;
}

// Filtros activos (GET).
$state = optional_param('state', '', PARAM_ALPHA);
$courseid = optional_param('courseid', 0, PARAM_INT);
$search = optional_param('search', '', PARAM_TEXT);
$datefrom = optional_param('datefrom', '', PARAM_RAW_TRIMMED);
$dateto = optional_param('dateto', '', PARAM_RAW_TRIMMED);
if ($state !== '' && !in_array($state, uploads::STATES, true)) {
    $state = '';
}
// Fechas en formato ISO del input nativo type="date"; inválidas se ignoran.
foreach (['datefrom', 'dateto'] as $datevar) {
    if ($$datevar !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $$datevar)) {
        $$datevar = '';
    }
}
$datefromts = $datefrom !== '' ? (int) strtotime($datefrom . ' 00:00:00') : 0;
$datetots = $dateto !== '' ? (int) strtotime($dateto . ' 23:59:59') : 0;

// La URL base incluye los filtros para que paginación y orden los conserven.
$urlparams = array_filter([
    'state' => $state,
    'courseid' => $courseid,
    'search' => $search,
    'datefrom' => $datefrom,
    'dateto' => $dateto,
]);
$url = new moodle_url('/mod/videoconnect/panel.php', $urlparams);

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('panel', 'mod_videoconnect'));
$PAGE->set_heading(get_string('panel', 'mod_videoconnect'));

// El controlador resuelve los datos (modelo) y los inyecta a las vistas.
// Los contadores respetan el ámbito de curso seleccionado arriba del panel.
$summary = uploads::get_summary($courseid);

// Feedback operativo: si hay subidas pendientes y la tarea no corre desde
// hace >10 minutos, el problema casi seguro es el cron — avisar con causa.
$cronwarning = null;
if (($summary[uploads::STATE_PENDING] ?? 0) > 0) {
    $task = task_manager::get_scheduled_task(upload_videos_task::class);
    if ($task) {
        $lastrun = (int) $task->get_last_run_time();
        if ($lastrun < time() - 600) {
            $cronwarning = get_string(
                'cronstale',
                'mod_videoconnect',
                $lastrun ? userdate($lastrun) : get_string('never')
            );
        }
    }
}

// Nombre del curso del filtro activo (para el preseleccionado del selector AJAX).
$coursename = '';
if ($courseid > 0) {
    // Sin escape: el template escapa al pintar (evita el doble escapado).
    $coursename = format_string(
        (string) $DB->get_field('course', 'fullname', ['id' => $courseid]),
        true,
        ['escape' => false]
    );
}

$table = new videos_table('mod_videoconnect_panel', $url, $state, $courseid, $search, $datefromts, $datetots);
ob_start();
$table->out(50, false);
$tablehtml = ob_get_clean();

// Distinguir "los filtros no casan" del vacío inicial del sitio: con ámbito
// de curso activo hace falta el total global (consulta extra, barata).
$hasactivities = $courseid > 0
    ? array_sum(uploads::get_summary(0)) > 0
    : array_sum($summary) > 0;

$output = $PAGE->get_renderer('mod_videoconnect');
echo $OUTPUT->header();
echo $output->render(new panel_page(
    $summary,
    [
        'state' => $state, 'courseid' => $courseid, 'coursename' => $coursename,
        'search' => $search, 'datefrom' => $datefrom, 'dateto' => $dateto,
    ],
    $tablehtml,
    (int) $table->totalrows,
    $hasactivities,
    $cronwarning,
    $settingsurl,
    $activeprovider
));
echo $OUTPUT->footer();
