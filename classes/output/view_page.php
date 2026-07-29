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
use mod_videoconnect\provider\provider_interface;
use mod_videoconnect\uploads;
use moodle_url;
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
 * The no-video situations render as a state card sharing the same 16:9
 * container as the player, so the page keeps its layout across states.
 * Technical error details are only exposed to users who can manage the
 * control panel; students always get a plain-language message.
 *
 * @package    mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class view_page implements renderable, templatable {
    /** @var string State card variant: upload queued/in progress/just completed. */
    protected const CARD_PENDING = 'pending';

    /** @var string State card variant: last upload failed. */
    protected const CARD_ERROR = 'error';

    /** @var string State card variant: no video configured at all. */
    protected const CARD_NOVIDEO = 'novideo';

    /** @var stdClass Activity instance record ({videoconnect}). */
    protected stdClass $instance;

    /** @var bool Has name? */
    protected bool $hasname;

    /** @var stdClass|null Latest {videoconnect_uploads} row, if relevant. */
    protected ?stdClass $lastupload;

    /** @var int Course module id (for intro formatting and files). */
    protected int $cmid;

    /** @var bool Whether the intro must be rendered. */
    protected bool $showdescription;

    /** @var bool Whether the user can manage videos (panel capability). */
    protected bool $canmanage;

    /** @var provider_interface|null Connector of the instance provider (embed URL). */
    protected ?provider_interface $provider;

    /**
     * view_page constructor.
     *
     * @param stdClass $instance Record from {videoconnect}.
     * @param bool $ithasname Whether to render the activity name heading.
     * @param stdClass|null $lastupload Latest upload attempt; only used when
     *        the instance has no idvideo yet (pending/failed upload state).
     * @param int $cmid Course module id of the instance.
     * @param bool $showdescription Whether to render the intro.
     * @param bool $canmanage Whether the user can use the control panel:
     *        enables the technical cause and the link to the detail page.
     * @param provider_interface|null $provider Connector of the provider the
     *        instance was created with (resolved by the caller); required to
     *        embed when the instance has a published video.
     */
    public function __construct(stdClass $instance, bool $ithasname = true, ?stdClass $lastupload = null,
            int $cmid = 0, bool $showdescription = true, bool $canmanage = false,
            ?provider_interface $provider = null) {
        $this->instance = $instance;
        $this->hasname = $ithasname;
        $this->lastupload = $lastupload;
        $this->cmid = $cmid;
        $this->showdescription = $showdescription;
        $this->canmanage = $canmanage;
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
        // Sin escape: el template ya escapa con {{name}} (evita el doble
        // escapado de format_string + Mustache).
        $data->name = format_string($this->instance->name, true, ['escape' => false]);
        $data->has_name = $this->hasname;
        $data->title = $data->name;
        $data->intro = '';
        if ($this->showdescription && trim((string) $this->instance->intro) !== '') {
            $data->intro = format_module_intro('videoconnect', $this->instance, $this->cmid);
        }
        $data->has_intro = ($data->intro !== '');

        if (!empty($this->instance->idvideo) && $this->provider !== null) {
            $data->has_vimeo = true;
            $data->embedurl = $this->provider->get_embed_url((string) $this->instance->idvideo);
            return $data;
        }

        $data->has_vimeo = false;
        $data->card = $this->export_card();
        return $data;
    }

    /**
     * Builds the state card (C3) shown instead of the player.
     *
     * @return stdClass
     * @throws coding_exception
     */
    protected function export_card(): stdClass {
        $variant = $this->resolve_variant();
        $status = $this->lastupload ? (int) $this->lastupload->status : null;
        $uploading = ($status === uploads::STATUS_UPLOADING);

        $card = new stdClass();
        $card->ispending = ($variant === self::CARD_PENDING);
        $card->iserror = ($variant === self::CARD_ERROR);
        $card->isnovideo = ($variant === self::CARD_NOVIDEO);
        $card->spin = $uploading;

        switch ($variant) {
            case self::CARD_PENDING:
                $card->icon = $uploading ? 'fa-rotate' : 'fa-clock';
                $card->title = get_string('card_pending_title', 'mod_videoconnect');
                $card->sub = get_string('card_pending_sub', 'mod_videoconnect');
                break;
            case self::CARD_ERROR:
                $card->icon = 'fa-triangle-exclamation';
                if ($this->canmanage) {
                    $card->title = get_string('card_error_mgmt_title', 'mod_videoconnect');
                    $card->sub = get_string('card_error_mgmt_sub', 'mod_videoconnect');
                    // Causa resumida solo para quien gestiona: la etiqueta de
                    // diagnóstico del estado, nunca el volcado HTTP completo.
                    $card->cause = $status !== null ? uploads::get_status_label($status) : '';
                } else {
                    $card->title = get_string('card_error_title', 'mod_videoconnect');
                    $card->sub = get_string('card_error_sub', 'mod_videoconnect');
                }
                break;
            default:
                $card->icon = 'fa-minus';
                $card->title = get_string('card_novideo_title', 'mod_videoconnect');
                $card->sub = get_string(
                    $this->canmanage ? 'card_novideo_sub_teacher' : 'card_novideo_sub_student',
                    'mod_videoconnect'
                );
        }

        $card->hascause = !empty($card->cause);
        $card->showdetail = $this->canmanage && $variant !== self::CARD_NOVIDEO;
        if ($card->showdetail) {
            $card->detailurl = (new moodle_url('/mod/videoconnect/panel.php',
                ['instanceid' => $this->instance->id]))->out(false);
        }
        return $card;
    }

    /**
     * Maps the latest upload status to a card variant.
     *
     * @return string One of the CARD_* constants.
     */
    protected function resolve_variant(): string {
        if (!$this->lastupload) {
            return self::CARD_NOVIDEO;
        }
        switch ((int) $this->lastupload->status) {
            case uploads::STATUS_NOT_EXECUTED:
            case uploads::STATUS_UPLOADING:
            case uploads::STATUS_COMPLETED:
                // Completada sin idvideo aún: el proveedor puede estar
                // transcodificando; para el usuario sigue "en breve".
                return self::CARD_PENDING;
            case uploads::STATUS_ERROR_UPLOADING:
            case uploads::STATUS_UPLOADING_VIDEOID_MISSING:
            case uploads::STATUS_UPLOADING_ERROR_WHITELIST:
            case uploads::STATUS_UPLOADING_ERROR_FOLDER:
            case uploads::STATUS_DELETED:
                return self::CARD_ERROR;
            default:
                // STATUS_DISCARDED, filas históricas STATUS_NOT_FILEPATH o
                // estados desconocidos: sin vídeo operativo.
                return self::CARD_NOVIDEO;
        }
    }
}
