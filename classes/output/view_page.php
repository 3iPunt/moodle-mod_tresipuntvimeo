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
 * Video Connect view renderable.
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
 * Main content renderable class.
 *
 * Pure view: it receives all the data it needs and performs no DB access.
 * Callers fetch the {videoconnect} record (and, when there is no published
 * video yet, the latest upload attempt via uploads::get_latest()).
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class view_page implements renderable, templatable {
    /** @var stdClass Activity instance record ({videoconnect}). */
    protected stdClass $instance;

    /** @var bool Has name? */
    protected bool $hasname;

    /** @var stdClass|null Latest {videoconnect_uploads} row, if relevant. */
    protected ?stdClass $lastupload;

    /**
     * view_page constructor.
     *
     * @param stdClass $instance Record from {videoconnect}.
     * @param bool $ithasname Whether to render the activity name heading.
     * @param stdClass|null $lastupload Latest upload attempt; only used when
     *        the instance has no idvideo yet (pending/failed upload state).
     */
    public function __construct(stdClass $instance, bool $ithasname = true, ?stdClass $lastupload = null) {
        $this->instance = $instance;
        $this->hasname = $ithasname;
        $this->lastupload = $lastupload;
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
        $data->name = $this->instance->name;
        $data->has_name = $this->hasname;
        $data->intro = $this->instance->intro;
        if (!empty($this->instance->idvideo)) {
            $data->idvideo = $this->instance->idvideo;
            $data->width = '640';
            $data->height = '360';
            $data->has_vimeo = true;
        } else {
            $data->has_vimeo = false;
            $data->title = $this->instance->name;
            if ($this->lastupload) {
                $data->status = get_string(
                    uploads::ERROR_MESSAGE[$this->lastupload->status],
                    'mod_videoconnect'
                );
                $data->http_error_message = $this->lastupload->http_error_message;
            }
        }

        return $data;
    }
}
