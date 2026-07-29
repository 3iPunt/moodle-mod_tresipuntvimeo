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
use mod_videoconnect\provider\provider_interface;
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
 * and derived state), the upload attempts with their flags and action URLs
 * already resolved by the controller (panel.php), and the last non-discarded
 * attempt feeding the diagnosis card.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class detail_page implements renderable, templatable {
    /** @var stdClass Activity row: id, name, idvideo, state, coursename, courseid, cmid. */
    protected stdClass $activity;

    /** @var stdClass[] Upload attempts, newest first, with flags and action URLs. */
    protected array $attempts;

    /** @var moodle_url URL back to the panel list. */
    protected moodle_url $backurl;

    /** @var stdClass|null Last non-discarded attempt (diagnosis input). */
    protected ?stdClass $lastlive;

    /** @var provider_interface|null Provider connector (branding and video URL). */
    protected ?provider_interface $provider;

    /**
     * detail_page constructor.
     *
     * @param stdClass $activity Activity row with course/cm data and derived state.
     * @param stdClass[] $attempts Upload attempts (each with fileavailable,
     *        canretry, candiscard, notrecoverable, retryurl, discardurl).
     * @param moodle_url $backurl URL back to the panel list.
     * @param stdClass|null $lastlive Last non-discarded attempt, if any.
     * @param provider_interface|null $provider Provider connector of the activity.
     */
    public function __construct(stdClass $activity, array $attempts, moodle_url $backurl,
            ?stdClass $lastlive = null, ?provider_interface $provider = null) {
        $this->activity = $activity;
        $this->attempts = $attempts;
        $this->backurl = $backurl;
        $this->lastlive = $lastlive;
        $this->provider = $provider;
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
        $data->logotresipunt = $output->image_url('tresipunt_logo', 'mod_videoconnect')->out(false);
        $logopix = $this->provider ? $this->provider->get_logo_pix() : 'icon';
        $data->logoprovider = $output->image_url($logopix, 'mod_videoconnect')->out(false);
        $data->providername = $this->provider ? $this->provider->get_display_name() : '';
        $data->backurl = $this->backurl->out(false);

        // Sin escape: el template ya escapa con {{...}} (evita el doble
        // escapado de format_string + Mustache).
        $data->name = format_string($this->activity->name, true, ['escape' => false]);
        $data->activityurl = (new moodle_url('/mod/videoconnect/view.php', ['id' => $this->activity->cmid]))->out(false);
        $data->coursename = format_string($this->activity->coursename, true, ['escape' => false]);
        $data->courseurl = (new moodle_url('/course/view.php', ['id' => $this->activity->courseid]))->out(false);
        $data->badge = badges::state_badge($this->activity->state);
        $data->hasvideo = !empty($this->activity->idvideo);
        $data->idvideo = $this->activity->idvideo;
        $data->videourl = ($data->hasvideo && $this->provider)
            ? $this->provider->get_video_url((string) $this->activity->idvideo)
            : '';

        $data->diag = $this->export_diag();
        $data->hasdiag = !empty($data->diag);

        $data->attempts = [];
        foreach ($this->attempts as $attempt) {
            $badge = badges::status_badge((int) $attempt->status);
            $data->attempts[] = [
                'timecreated' => userdate($attempt->timecreated, get_string('strftimedatetimeshort', 'langconfig')),
                'timeuploaded' => !empty($attempt->timeuploaded)
                    ? userdate($attempt->timeuploaded, get_string('strftimedatetimeshort', 'langconfig'))
                    : '—',
                'badge' => $badge,
                'note' => $this->attempt_note($attempt),
                'fileavailable' => !empty($attempt->fileavailable),
                'canretry' => !empty($attempt->canretry),
                'candiscard' => !empty($attempt->candiscard),
                'notrecoverable' => !empty($attempt->notrecoverable),
                'retryurl' => $attempt->retryurl->out(false),
                'discardurl' => $attempt->discardurl->out(false),
                'noactions' => empty($attempt->canretry) && empty($attempt->candiscard)
                    && empty($attempt->notrecoverable),
            ];
        }
        $data->hasattempts = !empty($data->attempts);
        $data->noattemptsnote = get_string(
            $data->hasvideo ? 'noattempts_linked' : 'noattempts',
            'mod_videoconnect'
        );
        return $data;
    }

    /**
     * Human note of an attempt: the provider error when there is one, the
     * diagnostic label otherwise (never raw internal keys).
     *
     * @param stdClass $attempt
     * @return string
     * @throws coding_exception
     */
    protected function attempt_note(stdClass $attempt): string {
        $note = trim(($attempt->http_error_message ?? '')
            . (!empty($attempt->http_error_code) ? ' (' . $attempt->http_error_code . ')' : ''));
        if ($note === '') {
            $note = uploads::get_status_label((int) $attempt->status);
        }
        return $note;
    }

    /**
     * Diagnosis card (C6 variant): translates the derived state + last live
     * attempt into what happened and what the manager can do about it.
     * Empty when the video is published without incident.
     *
     * @return array|null tone, icon, spin, title, body.
     * @throws coding_exception
     */
    protected function export_diag(): ?array {
        $state = $this->activity->state;
        if ($state === uploads::STATE_PUBLISHED) {
            return null;
        }
        $status = $this->lastlive ? (int) $this->lastlive->status : null;
        $fileavailable = $this->lastlive ? !empty($this->lastlive->fileavailable) : false;

        if ($state === uploads::STATE_INCIDENT) {
            $variant = ($status === uploads::STATUS_UPLOADING_ERROR_FOLDER) ? 'folder' : 'whitelist';
            return $this->diag('warning', 'fa-triangle-exclamation', false,
                'diag_incident_title', 'diag_incident_body_' . $variant);
        }
        if ($state === uploads::STATE_ERROR) {
            if ($status === uploads::STATUS_UPLOADING_VIDEOID_MISSING) {
                return $this->diag('danger', 'fa-circle-xmark', false,
                    'diag_error_noid_title', 'diag_error_noid_body');
            }
            if (!$fileavailable) {
                return $this->diag('danger', 'fa-circle-xmark', false,
                    'diag_error_norecover_title', 'diag_error_norecover_body');
            }
            return $this->diag('warning', 'fa-triangle-exclamation', false,
                'diag_error_retryable_title', 'diag_error_retryable_body');
        }
        if ($state === uploads::STATE_PENDING) {
            $uploading = ($status === uploads::STATUS_UPLOADING);
            return $this->diag('teal', $uploading ? 'fa-rotate' : 'fa-clock', $uploading,
                $uploading ? 'diag_pending_uploading_title' : 'diag_pending_queued_title',
                $uploading ? 'diag_pending_uploading_body' : 'diag_pending_queued_body');
        }
        return $this->diag('muted', 'fa-minus', false, 'diag_novideo_title', 'diag_novideo_body');
    }

    /**
     * Builds one diagnosis card entry.
     *
     * @param string $tone vc-tone-* suffix.
     * @param string $icon FontAwesome icon class.
     * @param bool $spin Whether the icon spins.
     * @param string $titlekey Lang key of the title.
     * @param string $bodykey Lang key of the body.
     * @return array
     * @throws coding_exception
     */
    protected function diag(string $tone, string $icon, bool $spin, string $titlekey, string $bodykey): array {
        return [
            'tone' => $tone,
            'icon' => $icon,
            'spin' => $spin,
            'title' => get_string($titlekey, 'mod_videoconnect'),
            'body' => get_string($bodykey, 'mod_videoconnect'),
        ];
    }
}
