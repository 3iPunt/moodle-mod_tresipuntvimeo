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

namespace mod_videoconnect\tasks;

use coding_exception;
use core\task\scheduled_task;
use dml_exception;
use mod_videoconnect\uploads;
use mod_videoconnect\vimeo;
use moodle_exception;
use stdClass;

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
        global $DB, $CFG;
        mtrace("***** STARTING PROCESS");

        $uploads = $DB->get_records(
            'videoconnect_uploads',
            [ 'status' => uploads::STATUS_NOT_EXECUTED ],
            'timecreated DESC',
            '*'
        );

        mtrace("Uploading videos: " . count($uploads));
        $vimeo = new vimeo();
        foreach ($uploads as $upload) {
            mtrace("- Instance: " . $upload->instance);
            try {
                [$course, $cm] = get_course_and_cm_from_instance($upload->instance, 'videoconnect');

                $filepath = $upload->filepath;

                $params = [
                    'name' => $cm->name,
                    'privacy' => [
                            'embed' => 'whitelist',
                    ],
                ];

                $dataobject = new stdClass();
                $dataobject->id = $upload->id;
                $dataobject->status = uploads::STATUS_UPLOADING;
                $DB->update_record('videoconnect_uploads', $dataobject);
                mtrace("* Uploading: " . $cm->name . " - Instance: " . $upload->instance);

                $response = $vimeo->upload($filepath, $params);
                mtrace("* Response: " . json_encode($response));

                if ($response->success) {
                    $idvideo = $this->get_idvideo_from_url($response->data);
                    if ($idvideo > 0) {
                        $datamodule = new stdClass();
                        $datamodule->id = $upload->instance;
                        $datamodule->idvideo = $idvideo;
                        $datamodule->timemodified = time();
                        $DB->update_record('videoconnect', $datamodule);
                        // Add Whitelist.
                        $domain = get_config('mod_videoconnect', 'whitelist');
                        $responsewl = $vimeo->add_domain_whitelist($idvideo, $domain);
                        mtrace("* Response Whitelist: " . json_encode($responsewl));
                        if ($responsewl->success) {
                            mtrace("* Updated whitelist: " . $domain . " | Id video: " . $idvideo);
                            $dataobject = new stdClass();
                            $dataobject->id = $upload->id;
                            $dataobject->http_response = $response->data;
                            $dataobject->status = uploads::STATUS_COMPLETED;
                            $dataobject->timeuploaded = time();
                            // Move to folder.
                            $folderid = get_config('mod_videoconnect', 'folderid');
                            if (!empty($folderid)) {
                                $responsefol = $vimeo->add_video_to_folder($idvideo, $folderid);
                                mtrace("* Response Folder: " . json_encode($responsefol));
                                if ($responsefol->success) {
                                    mtrace("* Moved to folder: " . $folderid . " | Id video: " . $idvideo);
                                } else {
                                    mtrace("* Error moving to folder: " . $folderid . " | Id video: " . $idvideo);
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
                            $dataobject->http_error_message = $responsewl->error->message;
                            $dataobject->http_error_code = $responsewl->error->code;
                            $dataobject->status = uploads::STATUS_UPLOADING_ERROR_WHITELIST;
                            $dataobject->error_message = uploads::ERROR_MESSAGE[uploads::STATUS_UPLOADING_ERROR_WHITELIST];
                            $dataobject->timeuploaded = time();
                            $DB->update_record('videoconnect_uploads', $dataobject);
                            mtrace("* Error updating whitelist: " . $domain . " | Id video: " . $idvideo);
                        }
                        mtrace("* Upload OK: " . $cm->name);
                    } else {
                        $dataobject = new stdClass();
                        $dataobject->id = $upload->id;
                        $dataobject->status = uploads::STATUS_ERROR_UPLOADING;
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
                rebuild_course_cache($course->id);
            } catch (moodle_exception $e) {
                $dataobject = new stdClass();
                $dataobject->id = $upload->id;
                $dataobject->status = uploads::STATUS_DELETED;
                $dataobject->error_message = uploads::ERROR_MESSAGE[uploads::STATUS_DELETED];
                $DB->update_record('videoconnect_uploads', $dataobject);
                mtrace("* UPLOAD NOT EXECUTE: El module not exists anymore (" . $upload->instance . ")");
            }

            mtrace("-");
        }
        mtrace("***** FINAL");
    }

    /**
     * Get Id Video from URL.
     *
     * @param string $url
     * @return int
     */
    protected function get_idvideo_from_url(string $url): int {
        $last = strrpos($url, "/");
        if ($last) {
            $idvideo = intval(substr($url, $last + 1));
        } else {
            $idvideo = intval($url);
        }
        return $idvideo;
    }
}
