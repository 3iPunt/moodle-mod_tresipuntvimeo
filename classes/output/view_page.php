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
 * Block Video Connect Support renderable
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoconnect\output;

use coding_exception;
use dml_exception;
use mod_videoconnect\uploads;
use renderable;
use renderer_base;
use stdClass;
use templatable;


/**
 * Main_content renderable class.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class view_page implements renderable, templatable {
    /** @var stdClass Course Module */
    protected $cm;

    /** @var bool Has name? */
    protected $hasname;

    /**
     * view_page constructor.
     *
     * @param int $cmid
     * @param bool $ithasname
     * @throws dml_exception
     */
    public function __construct(int $cmid, bool $ithasname = true) {
        global $DB;
        $this->hasname = $ithasname;
        $this->cm = $DB->get_record('course_modules', ['id' => $cmid]);
    }


    /**
     * Export for Template.
     *
     * @param renderer_base $output
     * @return stdClass
     * @throws dml_exception
     * @throws coding_exception
     */
    public function export_for_template(renderer_base $output): stdClass {
        global $DB;
        $vimeomodule = $DB->get_record('videoconnect', ['id' => $this->cm->instance]);
        $vimeoupload = $DB->get_records(
            'videoconnect_uploads',
            ['instance' => $this->cm->instance],
            'timecreated DESC',
            '*',
            0,
            1
        );
        $data = new stdClass();
        $data->name = $vimeomodule->name;
        $data->has_name = $this->hasname;
        $data->intro = $vimeomodule->intro;
        $data->is_completed = false;
        if (!empty($vimeomodule->idvideo)) {
            $data->idvideo = $vimeomodule->idvideo;
            $data->width = '640';
            $data->height = '360';
            $data->has_vimeo = true;
        } else {
            $data->has_vimeo = false;
            $data->title = $vimeomodule->name;
            if (!empty($vimeoupload)) {
                $vimeoupload = current($vimeoupload);
                $data->status = get_string(
                    uploads::ERROR_MESSAGE[$vimeoupload->status],
                    'mod_videoconnect'
                );
                $data->http_error_message = $vimeoupload->http_error_message;
            }
        }

        return $data;
    }
}
