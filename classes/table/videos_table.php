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
 * Control panel videos table.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoconnect\table;

use html_writer;
use mod_videoconnect\output\badges;
use mod_videoconnect\provider\provider_interface;
use mod_videoconnect\provider\provider_manager;
use mod_videoconnect\uploads;
use moodle_url;
use stdClass;
use table_sql;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/tablelib.php');

/**
 * Control panel videos table: one row per live Video Connect activity with
 * its derived state (see uploads::get_state_case_sql()) and the last upload
 * attempt joined in. Single query, server-side paging and sorting.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class videos_table extends table_sql {
    /** @var provider_interface Active provider connector (video links). */
    protected provider_interface $provider;

    /**
     * videos_table constructor.
     *
     * @param string $uniqueid
     * @param moodle_url $baseurl Base URL including the active filters.
     * @param string $state Derived state to filter by ('' = all).
     * @param int $courseid Course to filter by (0 = all).
     * @param string $search Activity name search ('' = none).
     * @param int $datefrom Minimum modification timestamp (0 = none).
     * @param int $dateto Maximum modification timestamp (0 = none).
     * @throws \coding_exception
     */
    public function __construct(string $uniqueid, moodle_url $baseurl, string $state = '', int $courseid = 0,
            string $search = '', int $datefrom = 0, int $dateto = 0) {
        global $DB;
        parent::__construct($uniqueid);

        $this->provider = provider_manager::get_active();
        $this->define_baseurl($baseurl);
        $this->define_columns(['name', 'state', 'idvideo', 'timemodified', 'actions']);
        $this->define_headers([
            get_string('activity'),
            get_string('videostate', 'mod_videoconnect'),
            get_string('video', 'mod_videoconnect'),
            get_string('lastupload', 'mod_videoconnect'),
            get_string('actions'),
        ]);
        $this->sortable(true, 'timemodified', SORT_DESC);
        $this->no_sorting('idvideo');
        $this->no_sorting('actions');
        $this->collapsible(false);
        $this->set_attribute('class', 'generaltable mod_videoconnect-panel-table');

        $case = uploads::get_state_case_sql();
        [$from, $params] = uploads::get_panel_from_sql();

        $fields = "v.id, v.name, v.idvideo, v.timecreated, v.timemodified,
                   c.id AS courseid, c.fullname AS coursename, cm.id AS cmid,
                   u.id AS uploadid, u.status AS uploadstatus,
                   u.error_message, u.http_error_message,
                   {$case} AS state";

        $where = '1 = 1';
        if ($state !== '') {
            $where .= " AND {$case} = :statefilter";
            $params['statefilter'] = $state;
        }
        if ($courseid > 0) {
            $where .= ' AND v.course = :courseidfilter';
            $params['courseidfilter'] = $courseid;
        }
        if ($search !== '') {
            $where .= ' AND ' . $DB->sql_like('v.name', ':namesearch', false);
            $params['namesearch'] = '%' . $DB->sql_like_escape($search) . '%';
        }
        // Rango sobre la fecha mostrada en la columna (modificación o, si no, creación).
        if ($datefrom > 0) {
            $where .= ' AND COALESCE(NULLIF(v.timemodified, 0), v.timecreated) >= :datefrom';
            $params['datefrom'] = $datefrom;
        }
        if ($dateto > 0) {
            $where .= ' AND COALESCE(NULLIF(v.timemodified, 0), v.timecreated) <= :dateto';
            $params['dateto'] = $dateto;
        }

        $this->set_sql($fields, $from, $where, $params);
        $this->set_count_sql("SELECT COUNT(1) FROM {$from} WHERE {$where}", $params);
    }

    /**
     * Activity name linked to its view page, with the course underneath.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_name(stdClass $row): string {
        $activityurl = new moodle_url('/mod/videoconnect/view.php', ['id' => $row->cmid]);
        $courseurl = new moodle_url('/course/view.php', ['id' => $row->courseid]);
        return html_writer::link($activityurl, format_string($row->name), ['class' => 'vc-row-title'])
            . html_writer::div(
                html_writer::link($courseurl, format_string($row->coursename), ['class' => 'text-muted']),
                'vc-row-sub small'
            );
    }

    /**
     * Derived state rendered as a badge (C1), with a short human message
     * underneath for error/incident rows: the admin sees what happened
     * without entering the detail.
     *
     * @param stdClass $row
     * @return string
     * @throws \coding_exception
     */
    public function col_state(stdClass $row): string {
        $html = $this->badge(badges::state_badge($row->state));
        if (in_array($row->state, [uploads::STATE_ERROR, uploads::STATE_INCIDENT], true)) {
            $message = trim($row->http_error_message ?? '');
            if ($message === '') {
                // error_message guarda claves internas (p. ej. id_video_missing):
                // mostrar la etiqueta de diagnóstico traducida, nunca la clave.
                $message = $row->uploadstatus !== null
                    ? uploads::get_status_label((int) $row->uploadstatus)
                    : trim($row->error_message ?? '');
            }
            if ($message !== '') {
                $html .= html_writer::div(
                    s(shorten_text($message, 70)),
                    'vc-row-sub small text-muted',
                    ['title' => s($message)]
                );
            }
        }
        return $html;
    }

    /**
     * Video ID linked to the provider.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_idvideo(stdClass $row): string {
        if (empty($row->idvideo)) {
            return html_writer::span('—', 'text-muted');
        }
        return html_writer::link(
            $this->provider->get_video_url((string) $row->idvideo),
            s($row->idvideo),
            ['target' => '_blank', 'rel' => 'noopener', 'class' => 'vc-link']
        );
    }

    /**
     * Last activity date plus a mini badge (C2) of the last upload attempt.
     *
     * @param stdClass $row
     * @return string
     * @throws \coding_exception
     */
    public function col_timemodified(stdClass $row): string {
        $time = $row->timemodified ?: $row->timecreated;
        $html = html_writer::span(
            $time ? userdate($time, get_string('strftimedatetimeshort', 'langconfig')) : '—'
        );
        if ($row->uploadstatus !== null) {
            $html .= html_writer::div($this->badge(badges::status_badge((int) $row->uploadstatus), true), 'mt-1');
        }
        return $html;
    }

    /**
     * Explicit action to the detail view, where retry/discard live.
     *
     * @param stdClass $row
     * @return string
     * @throws \coding_exception
     */
    public function col_actions(stdClass $row): string {
        $url = new moodle_url('/mod/videoconnect/panel.php', ['instanceid' => $row->id]);
        $label = get_string('viewdetail', 'mod_videoconnect');
        return html_writer::link(
            $url,
            html_writer::tag('i', '', ['class' => 'fa fa-eye', 'aria-hidden' => 'true']),
            ['class' => 'btn btn-outline-secondary btn-sm vc-action-btn', 'title' => $label, 'aria-label' => $label]
        );
    }

    /**
     * Renders a vc-badge pill from badge data (icon, tone, spin, label).
     *
     * @param array $badge Badge data from badges::state_badge()/status_badge().
     * @param bool $mini Whether to render the small row variant.
     * @return string
     */
    protected function badge(array $badge, bool $mini = false): string {
        $icon = html_writer::tag('i', '', [
            'class' => 'fa ' . $badge['icon'] . (!empty($badge['spin']) ? ' fa-spin' : ''),
            'aria-hidden' => 'true',
        ]);
        return html_writer::span(
            $icon . $badge['label'],
            'vc-badge vc-badge-' . $badge['tone'] . ($mini ? ' vc-badge-mini' : '')
        );
    }
}
