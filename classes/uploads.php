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
 * Uploads
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoconnect;

use dml_exception;
use mod_videoconnect_mod_form;
use stdClass;

/**
 * Uploads
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class uploads {
    /** @var int Status indicating no file path */
    const STATUS_NOT_FILEPATH = 0;

    /** @var int Status indicating not executed */
    const STATUS_NOT_EXECUTED = 1;

    /** @var int Status indicating discarded */
    const STATUS_DISCARDED = 2;

    /** @var int Status indicating uploading */
    const STATUS_UPLOADING = 3;

    /** @var int Status indicating error uploading */
    const STATUS_ERROR_UPLOADING = 4;

    /** @var int Status indicating completed */
    const STATUS_COMPLETED = 5;

    /** @var int Status indicating deleted */
    const STATUS_DELETED = 6;

    /** @var int Status indicating video ID missing */
    const STATUS_UPLOADING_VIDEOID_MISSING = 7;

    /** @var int Status indicating error with whitelist */
    const STATUS_UPLOADING_ERROR_WHITELIST = 8;

    /** @var int Status indicating error with folder */
    const STATUS_UPLOADING_ERROR_FOLDER = 9;

    /** @var array Error messages */
    const ERROR_MESSAGE = [
        'filepath_not_found',
        'not_executed',
        'discarded',
        'uploading',
        'error_uploading',
        'completed',
        'deleted',
        'id_video_missing',
        'error_whitelist',
        'error_folder',
    ];

    /** @var int Error code for no file path */
    const CODE_NOT_FILEPATH = 10001;

    /**
     * Update.
     *
     * @param object $moduleinstance
     * @param mod_videoconnect_mod_form $mform
     * @return object
     * @throws dml_exception
     */
    public static function update(object $moduleinstance, mod_videoconnect_mod_form $mform): object {
        global $DB;

        if ($mform->get_data()) {
            $filepath = $mform->save_temp_file('filevimeo');

            if (!empty($filepath)) {
                $olds = $DB->get_records(
                    'videoconnect_uploads',
                    ['instance' => $moduleinstance->instance, 'status' => self::STATUS_NOT_EXECUTED]
                );

                foreach ($olds as $old) {
                    $oldobject = new stdClass();
                    $oldobject->id = $old->id;
                    $oldobject->status = self::STATUS_DISCARDED;
                    $DB->update_record('videoconnect_uploads', $oldobject);
                }

                $moduleinstance->idvideo = '';

                $dataobject = new stdClass();
                $dataobject->instance = $moduleinstance->instance;
                $dataobject->filepath = $filepath;
                $dataobject->status = self::STATUS_NOT_EXECUTED;
                $dataobject->timecreated = time();
                $DB->insert_record('videoconnect_uploads', $dataobject);
            } else {
                $dataobject = new stdClass();
                $dataobject->instance = $moduleinstance->instance;
                $dataobject->status = self::STATUS_NOT_FILEPATH;
                $dataobject->error_message = self::ERROR_MESSAGE[0];
                $dataobject->error_code = self::CODE_NOT_FILEPATH;
                $dataobject->timecreated = time();
                $DB->insert_record('videoconnect_uploads', $dataobject);
            }
        }

        return $moduleinstance;
    }
}
