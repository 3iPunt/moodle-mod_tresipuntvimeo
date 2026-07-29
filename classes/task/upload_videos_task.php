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
 * Class upload_videos_task.
 *
 * @package     mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoconnect\task;

use coding_exception;
use core\task\scheduled_task;
use dml_exception;
use mod_videoconnect\error;
use mod_videoconnect\provider\provider_manager;
use mod_videoconnect\uploads;
use mod_videoconnect\videoconnect;
use moodle_exception;
use moodle_url;
use stdClass;
use Throwable;

/**
 * Class upload_videos_task
 *
 * @package     mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class upload_videos_task extends scheduled_task {
    /**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     * @throws coding_exception
     */
    public function get_name(): string {
        return get_string('task_upload_videos', 'mod_videoconnect');
    }

    /**
     * Execute the task.
     *
     * @throws dml_exception
     */
    public function execute(): void {
        global $DB;
        mtrace("***** STARTING PROCESS");

        // Rescate de filas atascadas en "subiendo": si un run anterior murió
        // (fatal, timeout, corte de red) la fila quedaba en ese estado para
        // siempre, fuera del alcance de reintentar y descartar.
        $stuck = $DB->get_records_select(
            'videoconnect_uploads',
            'status = :status AND timecreated < :cutoff',
            ['status' => uploads::STATUS_UPLOADING, 'cutoff' => time() - (6 * HOURSECS)]
        );
        foreach ($stuck as $row) {
            $dataobject = new stdClass();
            $dataobject->id = $row->id;
            $dataobject->status = uploads::STATUS_ERROR_UPLOADING;
            $dataobject->error_message = uploads::ERROR_MESSAGE[uploads::STATUS_ERROR_UPLOADING];
            $dataobject->http_error_message = get_string('error_upload_interrupted', 'mod_videoconnect');
            $DB->update_record('videoconnect_uploads', $dataobject);
            mtrace("* RESCUED: upload {$row->id} stuck in uploading state, marked as retryable error.");
        }

        $uploads = $DB->get_records(
            'videoconnect_uploads',
            [ 'status' => uploads::STATUS_NOT_EXECUTED ],
            'timecreated DESC',
            '*'
        );

        mtrace("Uploading videos: " . count($uploads));

        if (empty($uploads)) {
            // Nothing to upload: skip the provider client initialisation
            // (it may request an access token) and finish quietly.
            mtrace("***** FINAL");
            return;
        }

        // Las subidas nuevas van siempre por el conector activo del sitio
        // (el sello por instancia gobierna la reproducción, no la subida).
        $provider = provider_manager::get_active();
        try {
            $provider->connect();
        } catch (Throwable $e) {
            // Misconfiguration (missing credentials/scopes) or provider auth
            // failure. Pending uploads stay as STATUS_NOT_EXECUTED and will
            // be retried once the configuration is fixed.
            $settingsurl = new moodle_url('/admin/settings.php', ['section' => 'modsettingvideoconnect']);
            mtrace("***** ERROR: " . $provider->get_display_name()
                . " client could not be initialised: " . $e->getMessage());
            mtrace("***** Review the plugin configuration: " . $settingsurl->out(false));
            mtrace("***** Pending uploads are kept and will be retried on the next run.");
            throw $e;
        }

        foreach ($uploads as $upload) {
            mtrace("- Instance: " . $upload->instance);

            // El course module puede haber desaparecido desde que se encoló:
            // caso propio, separado del resto de errores (dml_exception
            // extiende moodle_exception y antes cualquier fallo de BD se
            // etiquetaba como "actividad eliminada").
            try {
                [$course, $cm] = get_course_and_cm_from_instance($upload->instance, 'videoconnect');
            } catch (moodle_exception $e) {
                $dataobject = new stdClass();
                $dataobject->id = $upload->id;
                $dataobject->status = uploads::STATUS_DELETED;
                $dataobject->error_message = uploads::ERROR_MESSAGE[uploads::STATUS_DELETED];
                $DB->update_record('videoconnect_uploads', $dataobject);
                mtrace("* SKIPPED: the course module no longer exists ("
                    . $upload->instance . "): " . $e->getMessage());
                mtrace("-");
                continue;
            }

            try {
                $filepath = $upload->filepath;

                // La política de privacidad (oculto del catálogo del
                // proveedor, sin descargas ni comentarios, embebido por
                // whitelist o público según el setting) la aplica el
                // conector; el orquestador solo decide la restricción.
                $usewhitelist = videoconnect::is_whitelist_enabled();

                $dataobject = new stdClass();
                $dataobject->id = $upload->id;
                $dataobject->status = uploads::STATUS_UPLOADING;
                $DB->update_record('videoconnect_uploads', $dataobject);
                mtrace("* Uploading: " . $cm->name . " - Instance: " . $upload->instance);

                $response = $provider->upload($filepath, $cm->name, $usewhitelist);
                mtrace("* Response: " . json_encode($response));

                if ($response->success) {
                    $idvideo = $provider->extract_videoid($response->data);
                    if ($idvideo > 0) {
                        $datamodule = new stdClass();
                        $datamodule->id = $upload->instance;
                        $datamodule->idvideo = $idvideo;
                        $datamodule->timemodified = time();
                        $DB->update_record('videoconnect', $datamodule);
                        // Add Whitelist (todos los dominios configurados).
                        $whitelistok = true;
                        $whitelisterror = null;
                        if ($usewhitelist) {
                            $domains = videoconnect::get_whitelist_domains();
                            if (empty($domains)) {
                                $whitelistok = false;
                                $whitelisterror = new error(4003,
                                    get_string('error_whitelist_nodomains', 'mod_videoconnect'));
                                mtrace("* Whitelist enabled but no domains configured");
                            }
                            foreach ($domains as $domain) {
                                $responsewl = $provider->add_domain_whitelist($idvideo, $domain);
                                mtrace("* Response Whitelist ({$domain}): " . json_encode($responsewl));
                                if (!$responsewl->success) {
                                    $whitelistok = false;
                                    $whitelisterror = $responsewl->error;
                                    break;
                                }
                                mtrace("* Updated whitelist: " . $domain . " | Id video: " . $idvideo);
                            }
                        } else {
                            mtrace("* Whitelist disabled: public embed | Id video: " . $idvideo);
                        }
                        if ($whitelistok) {
                            $dataobject = new stdClass();
                            $dataobject->id = $upload->id;
                            $dataobject->http_response = $response->data;
                            $dataobject->status = uploads::STATUS_COMPLETED;
                            $dataobject->timeuploaded = time();
                            // Move to folder.
                            $folderid = get_config('mod_videoconnect', 'folderid');
                            if (!empty($folderid)) {
                                $responsefol = $provider->add_video_to_folder($idvideo, $folderid);
                                mtrace("* Response Folder: " . json_encode($responsefol));
                                if ($responsefol->success) {
                                    mtrace("* Moved to folder: " . $folderid . " | Id video: " . $idvideo);
                                } else {
                                    // Non-blocking: the video is uploaded and playable, only
                                    // the folder move failed (it stays in the Vimeo root).
                                    mtrace("* Error moving to folder: " . $folderid . " | Id video: " . $idvideo);
                                    $dataobject->status = uploads::STATUS_UPLOADING_ERROR_FOLDER;
                                    $dataobject->http_error_message = $responsefol->error->message;
                                    $dataobject->http_error_code = $responsefol->error->code;
                                    $dataobject->error_message = uploads::ERROR_MESSAGE[uploads::STATUS_UPLOADING_ERROR_FOLDER];
                                }
                            }
                            $DB->update_record('videoconnect_uploads', $dataobject);
                        } else {
                            $dataobject = new stdClass();
                            $dataobject->id = $upload->id;
                            $dataobject->http_response = $response->data;
                            $dataobject->http_error_message = $whitelisterror->message;
                            $dataobject->http_error_code = $whitelisterror->code;
                            $dataobject->status = uploads::STATUS_UPLOADING_ERROR_WHITELIST;
                            $dataobject->error_message = uploads::ERROR_MESSAGE[uploads::STATUS_UPLOADING_ERROR_WHITELIST];
                            $dataobject->timeuploaded = time();
                            $DB->update_record('videoconnect_uploads', $dataobject);
                            mtrace("* Error updating whitelist | Id video: " . $idvideo);
                        }
                        // El vídeo ya está publicado (los estados de
                        // incidencia no son reintentables): el fichero
                        // temporal deja de ser necesario.
                        uploads::delete_temp_file($upload);
                        mtrace("* Upload OK: " . $cm->name);
                    } else {
                        $dataobject = new stdClass();
                        $dataobject->id = $upload->id;
                        $dataobject->status = uploads::STATUS_UPLOADING_VIDEOID_MISSING;
                        $dataobject->error_message = uploads::ERROR_MESSAGE[uploads::STATUS_UPLOADING_VIDEOID_MISSING];
                        $dataobject->timeuploaded = time();
                        $DB->update_record('videoconnect_uploads', $dataobject);
                        mtrace("* Upload ERROR - Can't find the Video Id: " . $response->data);
                    }
                } else {
                    $dataobject = new stdClass();
                    $dataobject->id = $upload->id;
                    $dataobject->status = uploads::STATUS_ERROR_UPLOADING;
                    $dataobject->http_error_message = $response->error->message;
                    $dataobject->http_error_code = $response->error->code;
                    $DB->update_record('videoconnect_uploads', $dataobject);
                    mtrace("* Upload ERROR: " . $cm->name);
                }
            } catch (Throwable $e) {
                // Cualquier fallo no controlado (red, BD, API): la fila queda
                // en error reintentable — nunca huérfana en "subiendo" — y la
                // causa se registra en el log de la tarea y en la propia fila.
                $dataobject = new stdClass();
                $dataobject->id = $upload->id;
                $dataobject->status = uploads::STATUS_ERROR_UPLOADING;
                $dataobject->error_message = uploads::ERROR_MESSAGE[uploads::STATUS_ERROR_UPLOADING];
                $dataobject->http_error_message = substr($e->getMessage(), 0, 900);
                $DB->update_record('videoconnect_uploads', $dataobject);
                mtrace("* UPLOAD ERROR (unexpected): " . $e->getMessage());
            }

            mtrace("-");
        }
        mtrace("***** FINAL");
    }
}
