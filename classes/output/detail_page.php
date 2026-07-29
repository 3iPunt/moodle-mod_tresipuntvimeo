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
 * Control panel detail renderable.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoconnect\output;

use coding_exception;
use mod_videoconnect\uploads;
use moodle_url;
use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * Upload attempts detail of one activity.
 *
 * Pure view (no DB access): receives the activity row (with course/cm data
 * and derived state), the upload attempts and the file-availability flags
 * from the controller (panel.php).
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class detail_page implements renderable, templatable {
    /** @var string[] Badge CSS class per upload attempt status. */
    protected const BADGES = [
        uploads::STATUS_NOT_FILEPATH => 'badge text-bg-secondary',
        uploads::STATUS_NOT_EXECUTED => 'badge text-bg-info',
        uploads::STATUS_DISCARDED => 'badge text-bg-secondary',
        uploads::STATUS_UPLOADING => 'badge text-bg-info',
        uploads::STATUS_ERROR_UPLOADING => 'badge text-bg-danger',
        uploads::STATUS_COMPLETED => 'badge text-bg-success',
        uploads::STATUS_DELETED => 'badge text-bg-secondary',
        uploads::STATUS_UPLOADING_VIDEOID_MISSING => 'badge text-bg-danger',
        uploads::STATUS_UPLOADING_ERROR_WHITELIST => 'badge text-bg-warning',
        uploads::STATUS_UPLOADING_ERROR_FOLDER => 'badge text-bg-warning',
    ];

    /** @var stdClass Activity row: id, name, idvideo, state, coursename, courseid, cmid. */
    protected stdClass $activity;

    /** @var stdClass[] Upload attempts, newest first, each with bool $fileavailable. */
    protected array $attempts;

    /** @var moodle_url URL back to the panel list. */
    protected moodle_url $backurl;

    /**
     * detail_page constructor.
     *
     * @param stdClass $activity Activity row with course/cm data and derived state.
     * @param stdClass[] $attempts Upload attempts (each with bool fileavailable).
     * @param moodle_url $backurl URL back to the panel list.
     */
    public function __construct(stdClass $activity, array $attempts, moodle_url $backurl) {
        $this->activity = $activity;
        $this->attempts = $attempts;
        $this->backurl = $backurl;
    }

    /**
     * Export for Template.
     *
     * @param renderer_base $output
     * @return stdClass
     * @throws coding_exception
     */
    public function export_for_template(renderer_base $output): stdClass {
        $data = new stdClass();
        $data->name = format_string($this->activity->name);
        $data->coursename = format_string($this->activity->coursename);
        $data->courseurl = (new moodle_url('/course/view.php', ['id' => $this->activity->courseid]))->out(false);
        $data->activityurl = (new moodle_url('/mod/videoconnect/view.php', ['id' => $this->activity->cmid]))->out(false);
        $data->statelabel = uploads::get_state_label($this->activity->state);
        $data->hasvideo = !empty($this->activity->idvideo);
        $data->idvideo = $this->activity->idvideo;
        $data->videourl = $data->hasvideo ? 'https://vimeo.com/' . $this->activity->idvideo : '';
        $data->backurl = $this->backurl->out(false);

        $data->attempts = [];
        foreach ($this->attempts as $attempt) {
            $actionparams = [
                'instanceid' => $this->activity->id,
                'uploadid' => $attempt->id,
                'sesskey' => sesskey(),
            ];
            $data->attempts[] = [
                'timecreated' => userdate($attempt->timecreated, get_string('strftimedatetimeshort', 'langconfig')),
                'timeuploaded' => !empty($attempt->timeuploaded)
                    ? userdate($attempt->timeuploaded, get_string('strftimedatetimeshort', 'langconfig'))
                    : '-',
                'statuslabel' => uploads::get_status_label((int) $attempt->status),
                'badgeclass' => self::BADGES[(int) $attempt->status] ?? 'badge text-bg-secondary',
                'httperror' => trim(($attempt->http_error_message ?? '')
                    . (!empty($attempt->http_error_code) ? ' (' . $attempt->http_error_code . ')' : '')),
                'fileavailable' => !empty($attempt->fileavailable),
                'canretry' => !empty($attempt->canretry),
                'candiscard' => !empty($attempt->candiscard),
                'notrecoverable' => !empty($attempt->notrecoverable),
                'retryurl' => (new moodle_url('/mod/videoconnect/panel.php', $actionparams + ['action' => 'retry']))->out(false),
                'discardurl' => (new moodle_url('/mod/videoconnect/panel.php',
                    $actionparams + ['action' => 'discard']))->out(false),
            ];
        }
        $data->hasattempts = !empty($data->attempts);
        return $data;
    }
}
