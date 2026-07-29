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
 * Badge presentation maps shared by the panel views.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoconnect\output;

use coding_exception;
use mod_videoconnect\uploads;

/**
 * Single source of truth for the state/status badges (C1/C2 of the design
 * system): FontAwesome icon, colour tone and label, consumed by the panel,
 * the videos table and the detail page so every screen renders the same
 * badge for the same state.
 *
 * Tones map to the vc-badge-* CSS classes in styles.css (theme-agnostic,
 * valid in Bootstrap 4 and 5).
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class badges {
    /** @var array[] Icon and tone per derived activity state (C1). */
    public const STATES = [
        uploads::STATE_PUBLISHED => ['icon' => 'fa-circle-check', 'tone' => 'success'],
        uploads::STATE_INCIDENT => ['icon' => 'fa-triangle-exclamation', 'tone' => 'warning'],
        uploads::STATE_PENDING => ['icon' => 'fa-clock', 'tone' => 'neutral'],
        uploads::STATE_ERROR => ['icon' => 'fa-circle-xmark', 'tone' => 'danger'],
        uploads::STATE_NOVIDEO => ['icon' => 'fa-minus', 'tone' => 'muted'],
    ];

    /** @var array[] Icon, tone and spin per upload attempt status (C2). */
    public const STATUSES = [
        uploads::STATUS_NOT_FILEPATH => ['icon' => 'fa-minus', 'tone' => 'muted', 'spin' => false],
        uploads::STATUS_NOT_EXECUTED => ['icon' => 'fa-clock', 'tone' => 'neutral', 'spin' => false],
        uploads::STATUS_DISCARDED => ['icon' => 'fa-xmark', 'tone' => 'muted', 'spin' => false],
        uploads::STATUS_UPLOADING => ['icon' => 'fa-rotate', 'tone' => 'teal', 'spin' => true],
        uploads::STATUS_ERROR_UPLOADING => ['icon' => 'fa-triangle-exclamation', 'tone' => 'danger', 'spin' => false],
        uploads::STATUS_COMPLETED => ['icon' => 'fa-circle-check', 'tone' => 'success', 'spin' => false],
        uploads::STATUS_DELETED => ['icon' => 'fa-minus', 'tone' => 'muted', 'spin' => false],
        uploads::STATUS_UPLOADING_VIDEOID_MISSING => ['icon' => 'fa-triangle-exclamation', 'tone' => 'danger', 'spin' => false],
        uploads::STATUS_UPLOADING_ERROR_WHITELIST => ['icon' => 'fa-triangle-exclamation', 'tone' => 'danger', 'spin' => false],
        uploads::STATUS_UPLOADING_ERROR_FOLDER => ['icon' => 'fa-triangle-exclamation', 'tone' => 'danger', 'spin' => false],
    ];

    /**
     * Badge data of a derived activity state.
     *
     * @param string $state One of uploads::STATES.
     * @return array icon, tone, spin, label.
     * @throws coding_exception
     */
    public static function state_badge(string $state): array {
        $map = self::STATES[$state] ?? self::STATES[uploads::STATE_NOVIDEO];
        return $map + ['spin' => false, 'label' => uploads::get_state_label($state)];
    }

    /**
     * Badge data of an upload attempt status.
     *
     * @param int $status One of the uploads::STATUS_* constants.
     * @return array icon, tone, spin, label.
     * @throws coding_exception
     */
    public static function status_badge(int $status): array {
        $map = self::STATUSES[$status] ?? self::STATUSES[uploads::STATUS_NOT_FILEPATH];
        return $map + ['label' => uploads::get_status_label($status)];
    }
}
