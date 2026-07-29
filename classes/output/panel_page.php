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
use mod_videoconnect\provider\provider_interface;
use mod_videoconnect\uploads;
use moodle_exception;
use moodle_url;
use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * Control panel renderable.
 *
 * Pure view (no DB access): receives the counters per state, the active
 * filters (with the course name already resolved by the controller), the
 * rendered table with its row count, and the optional cron warning. The
 * whole filter toolbar is a single GET form (course scope included), so it
 * keeps working without JavaScript; the AMD module only enhances the course
 * select with the core AJAX course selector.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class panel_page implements renderable, templatable {
    /** @var array Counters per derived state (state => count). */
    protected array $summary;

    /** @var array Active filters: state, courseid, coursename, search, datefrom, dateto. */
    protected array $filters;

    /** @var string Rendered table HTML. */
    protected string $table;

    /** @var int Rows in the current (filtered) table. */
    protected int $totalrows;

    /** @var bool Whether the whole site has at least one activity. */
    protected bool $hasactivities;

    /** @var string|null Warning message (e.g. stale cron), null when none. */
    protected ?string $warning;

    /** @var moodle_url|null Settings URL for users who can configure, null otherwise. */
    protected ?moodle_url $settingsurl;

    /** @var provider_interface|null Active provider connector (header branding). */
    protected ?provider_interface $provider;

    /**
     * panel_page constructor.
     *
     * @param array $summary Counters per derived state (state => count).
     * @param array $filters Active filters: state (string), courseid (int),
     *        coursename (string, resolved by the controller), search (string),
     *        datefrom (string ISO) and dateto (string ISO).
     * @param string $table Rendered table HTML.
     * @param int $totalrows Rows matched by the current filters.
     * @param bool $hasactivities Whether the site has any activity at all.
     * @param string|null $warning Warning message to highlight, if any.
     * @param moodle_url|null $settingsurl Plugin settings URL when the user may configure.
     * @param provider_interface|null $provider Active provider connector.
     */
    public function __construct(array $summary, array $filters, string $table, int $totalrows,
            bool $hasactivities, ?string $warning = null, ?moodle_url $settingsurl = null,
            ?provider_interface $provider = null) {
        $this->summary = $summary;
        $this->filters = $filters + [
            'state' => '', 'courseid' => 0, 'coursename' => '', 'search' => '',
            'datefrom' => '', 'dateto' => '',
        ];
        $this->table = $table;
        $this->totalrows = $totalrows;
        $this->hasactivities = $hasactivities;
        $this->warning = $warning;
        $this->settingsurl = $settingsurl;
        $this->provider = $provider;
    }

    /**
     * Export for Template.
     *
     * @param renderer_base $output
     * @return stdClass
     * @throws coding_exception|moodle_exception
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
        foreach (page_header::export_branding($output, $this->provider) as $field => $value) {
            $data->{$field} = $value;
        }
        $data->headertitle = get_string('panel', 'mod_videoconnect');
        $data->headerdesc = get_string('panel_desc', 'mod_videoconnect');
        $data->updated = userdate(time(), get_string('strftimedatetimeshort', 'langconfig'));
        $data->refreshurl = (new moodle_url('/mod/videoconnect/panel.php',
            $baseparams + ($currentstate !== '' ? ['state' => $currentstate] : [])))->out(false);
        $data->settingsurl = $this->settingsurl?->out(false);

        $data->warning = $this->warning;

        $data->counters = [];
        foreach (uploads::STATES as $state) {
            $active = ($state === $currentstate);
            // Las tarjetas actúan como filtro rápido: clic filtra por el
            // estado; clic sobre la activa quita el filtro (toggle).
            $params = $baseparams + ($active ? [] : ['state' => $state]);
            $badge = badges::state_badge($state);
            $data->counters[] = [
                'state' => $state,
                'label' => $badge['label'],
                'icon' => $badge['icon'],
                'tone' => $badge['tone'],
                'count' => $this->summary[$state] ?? 0,
                'active' => $active,
                'url' => (new moodle_url('/mod/videoconnect/panel.php', $params))->out(false),
            ];
        }

        $data->action = (new moodle_url('/mod/videoconnect/panel.php'))->out(false);
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
        $data->reseturl = (new moodle_url('/mod/videoconnect/panel.php'))->out(false);

        // Estados de la zona de resultados: tabla, "sin resultados" (hay
        // actividades pero los filtros no casan) o vacío inicial del sitio.
        $data->hasrows = ($this->totalrows > 0);
        $data->noresults = (!$data->hasrows && $this->hasactivities);
        $data->emptyinitial = !$this->hasactivities;
        $data->table = $this->table;
        return $data;
    }
}
