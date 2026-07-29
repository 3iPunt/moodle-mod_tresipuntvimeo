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
 * Co-branded page header exporter.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoconnect\output;

use mod_videoconnect\provider\provider_interface;
use renderer_base;

/**
 * Branding context of the co-branded header (templates/page_header.mustache):
 * Tresipunt logo + active provider logo and name. Shared by every panel view
 * so the header renders identically everywhere.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class page_header {
    /**
     * Branding fields consumed by the page_header template.
     *
     * @param renderer_base $output
     * @param provider_interface|null $provider Active/instance provider connector.
     * @return array logotresipunt, logoprovider, providername.
     */
    public static function export_branding(renderer_base $output, ?provider_interface $provider): array {
        return [
            'logotresipunt' => $output->image_url('tresipunt_logo', 'mod_videoconnect')->out(false),
            'logoprovider' => $output->image_url(
                $provider ? $provider->get_logo_pix() : 'icon',
                'mod_videoconnect'
            )->out(false),
            'providername' => $provider ? $provider->get_display_name() : '',
        ];
    }
}
