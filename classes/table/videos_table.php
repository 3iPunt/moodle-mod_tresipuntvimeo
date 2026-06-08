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
    /** @var string[] Badge CSS class per derived state. */
    protected const BADGES = [
        uploads::STATE_PUBLISHED => 'badge text-bg-success',
        uploads::STATE_INCIDENT => 'badge text-bg-warning',
        uploads::STATE_PENDING => 'badge text-bg-info',
        uploads::STATE_ERROR => 'badge text-bg-danger',
        uploads::STATE_NOVIDEO => 'badge text-bg-secondary',
    ];

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

        $this->define_baseurl($baseurl);
        $this->define_columns(['coursename', 'name', 'state', 'idvideo', 'uploads', 'timemodified']);
        $this->define_headers([
            get_string('course'),
            get_string('activity'),
            get_string('videostate', 'mod_videoconnect'),
            get_string('idvideo', 'mod_videoconnect'),
            get_string('uploadattempts', 'mod_videoconnect'),
            get_string('lastmodified'),
        ]);
        $this->sortable(true, 'timemodified', SORT_DESC);
        $this->no_sorting('idvideo');
        $this->no_sorting('uploads');
        $this->collapsible(false);
        $this->set_attribute('class', 'generaltable mod_videoconnect-panel-table');

        $case = uploads::get_state_case_sql();
        [$from, $params] = uploads::get_panel_from_sql();

        $fields = "v.id, v.name, v.idvideo, v.timecreated, v.timemodified,
                   c.id AS courseid, c.fullname AS coursename, cm.id AS cmid,
                   u.id AS uploadid, u.status AS uploadstatus,
                   u.error_message, u.http_error_message,
                   (SELECT COUNT(1) FROM {videoconnect_uploads} u3 WHERE u3.instance = v.id) AS numuploads,
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
     * Course name linked to the course.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_coursename(stdClass $row): string {
        $url = new moodle_url('/course/view.php', ['id' => $row->courseid]);
        return \html_writer::link($url, format_string($row->coursename));
    }

    /**
     * Activity name linked to its view page.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_name(stdClass $row): string {
        $url = new moodle_url('/mod/videoconnect/view.php', ['id' => $row->cmid]);
        return \html_writer::link($url, format_string($row->name));
    }

    /**
     * Derived state rendered as a badge, with the upload error message
     * underneath (truncated) for error/incident rows: the admin sees what
     * happened without entering the detail.
     *
     * @param stdClass $row
     * @return string
     * @throws \coding_exception
     */
    public function col_state(stdClass $row): string {
        $class = self::BADGES[$row->state] ?? 'badge text-bg-secondary';
        $html = \html_writer::span(uploads::get_state_label($row->state), $class);
        if (in_array($row->state, [uploads::STATE_ERROR, uploads::STATE_INCIDENT], true)) {
            $message = trim($row->http_error_message ?? '') ?: trim($row->error_message ?? '');
            if ($message !== '') {
                $html .= \html_writer::div(
                    s(shorten_text($message, 70)),
                    'small text-muted mt-1',
                    ['title' => s($message)]
                );
            }
        }
        return $html;
    }

    /**
     * Vimeo video ID linked to the video on Vimeo.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_idvideo(stdClass $row): string {
        if (empty($row->idvideo)) {
            return '-';
        }
        return \html_writer::link(
            'https://vimeo.com/' . $row->idvideo,
            s($row->idvideo),
            ['target' => '_blank', 'rel' => 'noopener']
        );
    }

    /**
     * Upload attempts: explicit button to the detail view, where the
     * retry/discard actions live.
     *
     * @param stdClass $row
     * @return string
     * @throws \coding_exception
     */
    public function col_uploads(stdClass $row): string {
        if (empty($row->numuploads)) {
            return '-';
        }
        $url = new moodle_url('/mod/videoconnect/panel.php', ['instanceid' => $row->id]);
        return \html_writer::link(
            $url,
            get_string('manageuploads', 'mod_videoconnect') . ' (' . $row->numuploads . ')',
            ['class' => 'btn btn-sm btn-outline-primary text-nowrap']
        );
    }

    /**
     * Last modification time.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_timemodified(stdClass $row): string {
        $time = $row->timemodified ?: $row->timecreated;
        return $time ? userdate($time, get_string('strftimedatetimeshort', 'langconfig')) : '-';
    }
}
