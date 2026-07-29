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

use coding_exception;
use context_module;
use dml_exception;
use mod_videoconnect\event\upload_discarded;
use mod_videoconnect\event\upload_retried;
use mod_videoconnect_mod_form;
use moodle_exception;
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

    /** @var int[] Statuses that allow a retry (failed uploads). */
    const RETRYABLE_STATUSES = [self::STATUS_ERROR_UPLOADING, self::STATUS_UPLOADING_VIDEOID_MISSING];

    /** @var int[] Statuses that allow a discard (queued or failed; never mid-upload). */
    const DISCARDABLE_STATUSES = [
        self::STATUS_NOT_EXECUTED,
        self::STATUS_ERROR_UPLOADING,
        self::STATUS_UPLOADING_VIDEOID_MISSING,
    ];

    /** @var string Derived state: video published and playable. */
    const STATE_PUBLISHED = 'published';

    /** @var string Derived state: video published but last upload had a non-blocking incident (whitelist/folder). */
    const STATE_INCIDENT = 'incident';

    /** @var string Derived state: upload queued or in progress. */
    const STATE_PENDING = 'pending';

    /** @var string Derived state: last upload failed, no video published. */
    const STATE_ERROR = 'error';

    /** @var string Derived state: activity without video nor relevant upload. */
    const STATE_NOVIDEO = 'novideo';

    /** @var string[] All derived states, in display order. */
    const STATES = [
        self::STATE_PUBLISHED,
        self::STATE_INCIDENT,
        self::STATE_PENDING,
        self::STATE_ERROR,
        self::STATE_NOVIDEO,
    ];

    /**
     * SQL CASE expression deriving the panel state of an activity.
     *
     * Expects $valias pointing to {videoconnect} and $ualias to the LAST
     * upload row of the instance (LEFT JOIN, may be NULL).
     *
     * @param string $valias Alias of {videoconnect}.
     * @param string $ualias Alias of the last {videoconnect_uploads} row.
     * @return string
     */
    public static function get_state_case_sql(string $valias = 'v', string $ualias = 'u'): string {
        $statusin = function (array $statuses) use ($ualias): string {
            return $ualias . '.status IN (' . implode(',', $statuses) . ')';
        };
        $hasvideo = "COALESCE({$valias}.idvideo, '') <> ''";
        $incident = $statusin([self::STATUS_UPLOADING_ERROR_WHITELIST, self::STATUS_UPLOADING_ERROR_FOLDER]);
        $pending = $statusin([self::STATUS_NOT_EXECUTED, self::STATUS_UPLOADING]);
        $error = $statusin([self::STATUS_ERROR_UPLOADING, self::STATUS_UPLOADING_VIDEOID_MISSING]);
        return "CASE
                    WHEN {$hasvideo} AND {$incident} THEN '" . self::STATE_INCIDENT . "'
                    WHEN {$hasvideo} THEN '" . self::STATE_PUBLISHED . "'
                    WHEN {$pending} THEN '" . self::STATE_PENDING . "'
                    WHEN {$error} THEN '" . self::STATE_ERROR . "'
                    ELSE '" . self::STATE_NOVIDEO . "'
                END";
    }

    /**
     * SQL JOINs shared by the panel queries: live activities only (existing
     * course module) plus their last upload row (by id: inserts are
     * monotonic, so MAX(id) is the latest attempt).
     *
     * @return array [string $fromsql, array $params]
     */
    public static function get_panel_from_sql(): array {
        return [
            '{videoconnect} v
             JOIN {course} c ON c.id = v.course
             JOIN {modules} m ON m.name = :vcmodname
             JOIN {course_modules} cm ON cm.instance = v.id AND cm.module = m.id
                  AND cm.deletioninprogress = 0
             LEFT JOIN {videoconnect_uploads} u ON u.id = (
                 SELECT MAX(u2.id) FROM {videoconnect_uploads} u2 WHERE u2.instance = v.id
             )',
            ['vcmodname' => 'videoconnect'],
        ];
    }

    /**
     * Counters per derived state for the panel header.
     *
     * @param int $courseid Scope the counters to one course (0 = whole site).
     * @return array state => count, every state present (0 when none).
     * @throws dml_exception
     */
    public static function get_summary(int $courseid = 0): array {
        global $DB;
        [$fromsql, $params] = self::get_panel_from_sql();
        $case = self::get_state_case_sql();
        $where = '';
        if ($courseid > 0) {
            $where = ' WHERE v.course = :summarycourseid';
            $params['summarycourseid'] = $courseid;
        }
        $records = $DB->get_records_sql(
            "SELECT {$case} AS state, COUNT(*) AS num FROM {$fromsql}{$where} GROUP BY {$case}",
            $params
        );
        $summary = array_fill_keys(self::STATES, 0);
        foreach ($records as $record) {
            $summary[$record->state] = (int) $record->num;
        }
        return $summary;
    }

    /**
     * Human label of a derived state.
     *
     * @param string $state One of self::STATES.
     * @return string
     * @throws coding_exception
     */
    public static function get_state_label(string $state): string {
        return get_string('state_' . $state, 'mod_videoconnect');
    }

    /**
     * Admin-facing label of an upload attempt status.
     *
     * The strings mapped by ERROR_MESSAGE are phrased for the student view;
     * the panel needs diagnostic wording instead.
     *
     * @param int $status One of the STATUS_* constants.
     * @return string
     * @throws coding_exception
     */
    public static function get_status_label(int $status): string {
        if ($status < 0 || $status >= count(self::ERROR_MESSAGE)) {
            return get_string('unknown', 'core');
        }
        return get_string('uploadstatus_' . $status, 'mod_videoconnect');
    }

    /**
     * Every upload attempt of an instance, newest first.
     *
     * @param int $instanceid {videoconnect} id.
     * @return stdClass[]
     * @throws dml_exception
     */
    public static function get_attempts(int $instanceid, int $limit = 100): array {
        global $DB;
        // Acotado: el detalle muestra el historial reciente; una actividad
        // con cientos de reintentos no debe cargarlo entero en memoria.
        return $DB->get_records(
            'videoconnect_uploads',
            ['instance' => $instanceid],
            'timecreated DESC, id DESC',
            '*',
            0,
            $limit
        );
    }

    /**
     * Deletes the temp file of an upload attempt, if it still exists.
     *
     * @param stdClass $upload {videoconnect_uploads} row.
     */
    public static function delete_temp_file(stdClass $upload): void {
        if (!empty($upload->filepath) && is_file($upload->filepath)) {
            unlink($upload->filepath);
        }
    }

    /**
     * Whether an upload attempt can be requeued.
     *
     * Guards (model level, not only UI): failed status, no published video
     * on the instance (anti-duplicate) and the temp file still on disk.
     *
     * @param stdClass $upload {videoconnect_uploads} row.
     * @param stdClass $instance {videoconnect} row.
     * @return bool
     */
    public static function is_retryable(stdClass $upload, stdClass $instance): bool {
        return in_array((int) $upload->status, self::RETRYABLE_STATUSES, true)
            && empty($instance->idvideo)
            && !empty($upload->filepath)
            && file_exists($upload->filepath);
    }

    /**
     * Requeues a failed upload attempt for the cron task.
     *
     * @param int $uploadid {videoconnect_uploads} id.
     * @throws \moodle_exception When the attempt is not retryable.
     * @throws dml_exception
     */
    public static function retry(int $uploadid): void {
        global $DB;
        $upload = $DB->get_record('videoconnect_uploads', ['id' => $uploadid], '*', MUST_EXIST);
        $instance = $DB->get_record('videoconnect', ['id' => $upload->instance], '*', MUST_EXIST);
        // Guardas con causa específica: el panel muestra al usuario qué ha
        // pasado exactamente cuando la acción no es posible.
        if (!in_array((int) $upload->status, self::RETRYABLE_STATUSES, true)) {
            throw new \moodle_exception('cannotretry_status', 'mod_videoconnect');
        }
        if (!empty($instance->idvideo)) {
            throw new \moodle_exception('cannotretry_published', 'mod_videoconnect');
        }
        if (empty($upload->filepath) || !file_exists($upload->filepath)) {
            throw new \moodle_exception('cannotretry_filemissing', 'mod_videoconnect');
        }
        $DB->set_field('videoconnect_uploads', 'status', self::STATUS_NOT_EXECUTED, ['id' => $upload->id]);

        [, $cm] = get_course_and_cm_from_instance($instance->id, 'videoconnect');
        \mod_videoconnect\event\upload_retried::create([
            'objectid' => $upload->id,
            'context' => \context_module::instance($cm->id),
            'other' => ['instanceid' => $instance->id],
        ])->trigger();
    }

    /**
     * Discards a queued or failed upload attempt.
     *
     * Mid-upload attempts (STATUS_UPLOADING) cannot be discarded: that
     * would race with the cron task.
     *
     * @param int $uploadid {videoconnect_uploads} id.
     * @throws moodle_exception When the attempt is not discardable.
     * @throws dml_exception
     */
    public static function discard(int $uploadid): void {
        global $DB;
        $upload = $DB->get_record('videoconnect_uploads', ['id' => $uploadid], '*', MUST_EXIST);
        if (!in_array((int) $upload->status, self::DISCARDABLE_STATUSES, true)) {
            throw new \moodle_exception('cannotdiscard', 'mod_videoconnect');
        }
        $DB->set_field('videoconnect_uploads', 'status', self::STATUS_DISCARDED, ['id' => $upload->id]);

        [, $cm] = get_course_and_cm_from_instance($upload->instance, 'videoconnect');
        \mod_videoconnect\event\upload_discarded::create([
            'objectid' => $upload->id,
            'context' => \context_module::instance($cm->id),
            'other' => ['instanceid' => (int) $upload->instance],
        ])->trigger();
    }

    /**
     * Panel row of one live activity (course/cm data + derived state).
     *
     * @param int $instanceid {videoconnect} id.
     * @return stdClass
     * @throws dml_exception
     */
    public static function get_panel_row(int $instanceid): stdClass {
        global $DB;
        [$from, $params] = self::get_panel_from_sql();
        $case = self::get_state_case_sql();
        $params['instanceid'] = $instanceid;
        return $DB->get_record_sql(
            "SELECT v.id, v.name, v.idvideo, c.id AS courseid, c.fullname AS coursename,
                    cm.id AS cmid, {$case} AS state
               FROM {$from}
              WHERE v.id = :instanceid",
            $params,
            MUST_EXIST
        );
    }

    /**
     * Returns the most recent upload attempt for an instance, if any.
     *
     * @param int $instanceid {videoconnect} id.
     * @return stdClass|null
     * @throws dml_exception
     */
    public static function get_latest(int $instanceid): ?stdClass {
        global $DB;
        $records = $DB->get_records(
            'videoconnect_uploads',
            ['instance' => $instanceid],
            // Desempate por id: descartar e insertar caben en el mismo
            // segundo y sin él podía devolverse la fila descartada.
            'timecreated DESC, id DESC',
            '*',
            0,
            1
        );
        return $records ? current($records) : null;
    }

    /**
     * Update.
     *
     * Saving the form without picking a new file (embed-by-id mode or a
     * simple name/intro edit) is a normal action: no upload row is created.
     *
     * @param object $moduleinstance
     * @param mod_videoconnect_mod_form|null $mform Null when the instance is
     *        created programmatically (no form submission involved).
     * @return object
     * @throws dml_exception
     */
    public static function update(object $moduleinstance, ?mod_videoconnect_mod_form $mform = null): object {
        global $DB;

        if ($mform && $mform->get_data()) {
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
            }
        }

        return $moduleinstance;
    }
}
