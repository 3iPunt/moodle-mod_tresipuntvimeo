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
 * Course instances list renderable.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoconnect\output;

use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * List of every Video Connect instance of one course (index.php).
 *
 * Pure view: receives the rows already resolved by the controller (section
 * or date label, view URL, formatted name and intro, visibility).
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class index_page implements renderable, templatable {
    /** @var string Header of the first column (section name or last modified). */
    protected string $firstcolumn;

    /** @var array[] Rows: sectionlabel, viewurl, name, intro, dimmed. */
    protected array $rows;

    /**
     * index_page constructor.
     *
     * @param string $firstcolumn Header of the first column.
     * @param array[] $rows Rows with sectionlabel, viewurl, name, intro, dimmed.
     */
    public function __construct(string $firstcolumn, array $rows) {
        $this->firstcolumn = $firstcolumn;
        $this->rows = $rows;
    }

    /**
     * Export for Template.
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output): stdClass {
        $data = new stdClass();
        $data->firstcolumn = $this->firstcolumn;
        $data->rows = array_values($this->rows);
        return $data;
    }
}
