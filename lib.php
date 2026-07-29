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
use mod_videoconnect\output\view_page;
use mod_videoconnect\provider\provider_manager;
use mod_videoconnect\uploads;
use mod_videoconnect\videoconnect;
/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return bool|string|null True/archetype if the feature is supported, null otherwise.
 */
function videoconnect_supports(string $feature) {
    switch ($feature) {
        case FEATURE_BACKUP_MOODLE2:
        case FEATURE_MOD_INTRO:
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_MOD_PURPOSE:
            return MOD_PURPOSE_CONTENT;
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
 * @param mod_videoconnect_mod_form|null $mform The form.
 * @return int The id of the newly inserted record.
 * @throws dml_exception
 */
function videoconnect_add_instance(object $moduleinstance, ?mod_videoconnect_mod_form $mform = null): int {
    global $DB;
    $moduleinstance->timecreated = time();
    // Sello de proveedor: la instancia queda ligada al proveedor activo en
    // el momento de crearla y se reproducirá siempre con su conector,
    // aunque el sitio cambie de proveedor más adelante.
    $moduleinstance->provider = provider_manager::get_active_name();
    $id = $DB->insert_record('videoconnect', $moduleinstance);
    $moduleinstance->instance = $id;
    uploads::update($moduleinstance, $mform);
    // El modelo vacía idvideo en memoria cuando se ha subido un fichero
    // (el vídeo nuevo sustituirá al referenciado); persistirlo para que la
    // actividad no embeba el ID antiguo mientras corre el cron.
    if (($moduleinstance->idvideo ?? null) === '') {
        $DB->set_field('videoconnect', 'idvideo', '', ['id' => $id]);
    }
    return $id;
}

/**
 * Sets the activity content shown inline on the course page.
 *
 * The render happens here — at actual view time, when $PAGE is fully
 * initialised — instead of in get_coursemodule_info(): that callback runs
 * during course cache rebuilds (cron, CLI, restore), where rendering a
 * template and querying per instance is both fragile and wasteful.
 *
 * @param cm_info $cm
 * @throws coding_exception
 * @throws moodle_exception
 */
function videoconnect_cm_info_view(cm_info $cm): void {
    global $PAGE;
    // Todas las instancias del curso en una consulta cacheada por petición:
    // la página del curso renderiza cada actividad inline y disparaba una
    // consulta por instancia.
    $instances = videoconnect::get_course_instances((int) $cm->course);
    if (!isset($instances[$cm->instance])) {
        return;
    }
    $instance = $instances[$cm->instance];
    // El docente decide por instancia si el vídeo se embebe en la página del
    // curso; desactivado, el curso muestra el enlace estándar a view.php.
    // Las filas anteriores al campo (pre-upgrade) conservan el embebido.
    if (isset($instance->displayinline) && !$instance->displayinline) {
        return;
    }
    $lastupload = empty($instance->idvideo) ? uploads::get_latest($instance->id) : null;
    $canmanage = has_capability('mod/videoconnect:managevideos', context_system::instance());
    // En la página del curso la descripción la decide el docente con el
    // ajuste estándar "Muestra la descripción" (FEATURE_SHOW_DESCRIPTION).
    $showdescription = !empty($cm->showdescription);
    $provider = provider_manager::get_provider($instance->provider ?? provider_manager::DEFAULT);
    $output = $PAGE->get_renderer('mod_videoconnect');
    $cm->set_content(
        $output->render(new view_page($instance, false, $lastupload, $cm->id, $showdescription, $canmanage, $provider)),
        true
    );
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

    // Los ficheros temporales de las subidas quedaban huérfanos en disco a
    // merced de la limpieza de temp del core: borrarlos con sus filas.
    $uploads = $DB->get_records('videoconnect_uploads', ['instance' => $id], '', 'id, filepath');
    foreach ($uploads as $upload) {
        uploads::delete_temp_file($upload);
    }

    $DB->delete_records('videoconnect_uploads', [
        'instance' => $id,
    ]);

    $DB->delete_records('videoconnect', ['id' => $id]);
    return true;
}
