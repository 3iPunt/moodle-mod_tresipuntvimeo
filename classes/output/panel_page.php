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
 * Control panel renderable.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoconnect\output;

use coding_exception;
use mod_videoconnect\uploads;
use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * Control panel renderable.
 *
 * Pure view (no DB access): receives the counters per state, the active
 * filters (with the course name already resolved by the controller), the
 * rendered table and the optional warning. The filter toolbar is built in
 * the template (the course field is enhanced with the core AJAX course
 * selector) so every control shares the same layout and height.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class panel_page implements renderable, templatable {
    /** @var string[] Card border CSS class per derived state. */
    protected const CARDS = [
        uploads::STATE_PUBLISHED => 'border-success',
        uploads::STATE_INCIDENT => 'border-warning',
        uploads::STATE_PENDING => 'border-info',
        uploads::STATE_ERROR => 'border-danger',
        uploads::STATE_NOVIDEO => 'border-secondary',
    ];

    /** @var string[] Badge CSS class per derived state (same key as the table). */
    protected const BADGES = [
        uploads::STATE_PUBLISHED => 'badge text-bg-success',
        uploads::STATE_INCIDENT => 'badge text-bg-warning',
        uploads::STATE_PENDING => 'badge text-bg-info',
        uploads::STATE_ERROR => 'badge text-bg-danger',
        uploads::STATE_NOVIDEO => 'badge text-bg-secondary',
    ];

    /** @var array Counters per derived state (state => count). */
    protected array $summary;

    /** @var array Active filters: state, courseid, coursename, search. */
    protected array $filters;

    /** @var string Rendered table HTML. */
    protected string $table;

    /** @var string|null Warning message (e.g. stale cron), null when none. */
    protected ?string $warning;

    /**
     * panel_page constructor.
     *
     * @param array $summary Counters per derived state (state => count).
     * @param array $filters Active filters: state (string), courseid (int),
     *        coursename (string, resolved by the controller) and search (string).
     * @param string $table Rendered table HTML.
     * @param string|null $warning Warning message to highlight, if any.
     */
    public function __construct(array $summary, array $filters, string $table, ?string $warning = null) {
        $this->summary = $summary;
        $this->filters = $filters + [
            'state' => '', 'courseid' => 0, 'coursename' => '', 'search' => '',
            'datefrom' => '', 'dateto' => '',
        ];
        $this->table = $table;
        $this->warning = $warning;
    }

    /**
     * Export for Template.
     *
     * @param renderer_base $output
     * @return stdClass
     * @throws coding_exception
     */
    public function export_for_template(renderer_base $output): stdClass {
        $currentstate = (string) $this->filters['state'];
        $baseparams = array_filter([
            'courseid' => (int) $this->filters['courseid'],
            'search' => (string) $this->filters['search'],
            'datefrom' => (string) $this->filters['datefrom'],
            'dateto' => (string) $this->filters['dateto'],
        ]);

        $data = new stdClass();
        $data->counters = [];
        foreach (uploads::STATES as $state) {
            $active = ($state === $currentstate);
            // Las tarjetas actúan como filtro rápido: clic filtra por el
            // estado; clic sobre la activa quita el filtro (toggle).
            $params = $baseparams + ($active ? [] : ['state' => $state]);
            $data->counters[] = [
                'state' => $state,
                'label' => uploads::get_state_label($state),
                'count' => $this->summary[$state] ?? 0,
                'cardclass' => self::CARDS[$state] ?? 'border-secondary',
                'badgeclass' => self::BADGES[$state] ?? 'badge text-bg-secondary',
                'active' => $active,
                'url' => (new \moodle_url('/mod/videoconnect/panel.php', $params))->out(false),
            ];
        }

        $data->action = (new \moodle_url('/mod/videoconnect/panel.php'))->out(false);
        $data->currentstate = $currentstate;
        $data->states = [];
        foreach (uploads::STATES as $state) {
            $data->states[] = [
                'value' => $state,
                'label' => uploads::get_state_label($state),
                'selected' => ($state === $currentstate),
            ];
        }
        $data->courseid = (int) $this->filters['courseid'] ?: null;
        $data->coursename = (string) $this->filters['coursename'];
        $data->search = (string) $this->filters['search'];
        $data->datefrom = (string) $this->filters['datefrom'];
        $data->dateto = (string) $this->filters['dateto'];
        $data->hasfilters = ($currentstate !== '' || !empty($this->filters['courseid'])
            || $data->search !== '' || $data->datefrom !== '' || $data->dateto !== '');
        // El reset limpia estado y búsqueda pero conserva el ámbito de curso.
        $data->reseturl = (new \moodle_url('/mod/videoconnect/panel.php',
            $data->courseid ? ['courseid' => $data->courseid] : []))->out(false);

        $data->table = $this->table;
        $data->warning = $this->warning;
        return $data;
    }
}
