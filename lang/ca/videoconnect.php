<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here (Catalan).
 *
 * Only the strings introduced by the control panel (TIPVIDEOC-8) are
 * translated here; the rest of the plugin falls back to English on purpose
 * (completing the Catalan translation is out of the scope of that ticket).
 *
 * @package     mod_videoconnect
 * @category    string
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author      3IPUNT <contacte@tresipunt.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['backtopanel'] = 'Tornar al tauler de control';
$string['cannotdiscard'] = 'No es pot descartar: el seu estat actual no ho permet (una pujada en curs no es pot descartar).';
$string['cannotretry_filemissing'] = 'No es pot reintentar: el fitxer temporal ja no existeix. Torna a pujar el vídeo des de l\'activitat.';
$string['cannotretry_published'] = 'No es pot reintentar: l\'activitat ja té un vídeo publicat (reintentar el duplicaria a Vimeo).';
$string['cannotretry_status'] = 'No es pot reintentar: el seu estat actual no ho permet (potser una altra persona l\'ha processat o descartat mentrestant).';
$string['cronstale'] = 'Hi ha pujades pendents però la tasca de pujada no s\'executa des de {$a}. Comprova que el cron de Moodle està actiu.';
$string['confirmdiscard'] = 'Vols descartar aquesta pujada? No es processarà.';
$string['confirmretry'] = 'Vols tornar a encuar aquesta pujada? La tasca programada la tornarà a processar.';
$string['discard'] = 'Descartar';
$string['discarddone'] = 'La pujada s\'ha descartat.';
$string['eventuploaddiscarded'] = 'Intent de pujada descartat';
$string['eventuploadretried'] = 'Intent de pujada tornat a encuar';
$string['notrecoverable'] = 'No recuperable: torna a pujar el vídeo des de l\'activitat';
$string['retry'] = 'Reintentar';
$string['retrydone'] = 'La pujada s\'ha tornat a encuar.';
$string['errordetail'] = 'Detall de l\'error';
$string['fileavailable'] = 'Fitxer disponible';
$string['filter_allcourses'] = 'Tots els cursos';
$string['filter_allstates'] = 'Tots els estats';
$string['fromdate'] = 'Des de';
$string['noattempts'] = 'Aquesta activitat no té intents de pujada (mode "ID existent" o sense vídeo encara).';
$string['todate'] = 'Fins a';
$string['uploadattempts'] = 'Intents de pujada';
$string['uploaddate'] = 'Data de pujada';
$string['uploadstatus_0'] = 'Sense fitxer seleccionat';
$string['uploadstatus_1'] = 'En cua';
$string['uploadstatus_2'] = 'Descartada';
$string['uploadstatus_3'] = 'Pujant';
$string['uploadstatus_4'] = 'Pujada fallida';
$string['uploadstatus_5'] = 'Completada';
$string['uploadstatus_6'] = 'Activitat eliminada';
$string['uploadstatus_7'] = 'Resposta sense ID de vídeo';
$string['uploadstatus_8'] = 'Ha fallat la whitelist de domini';
$string['uploadstatus_9'] = 'Ha fallat el moviment a carpeta';
$string['manageuploads'] = 'Gestionar';
$string['panel'] = 'Tauler de control de Video Connect';
$string['state_error'] = 'Error de pujada';
$string['state_incident'] = 'Publicat amb incidència';
$string['state_novideo'] = 'Sense vídeo';
$string['state_pending'] = 'Pendent de pujar';
$string['state_published'] = 'Publicat';
$string['videostate'] = 'Estat del vídeo';
$string['usewhitelist'] = 'Restringir la incrustació als dominis de la whitelist';
$string['usewhitelist_desc'] = '<strong>Avís:</strong> si es desactiva, els vídeos pujats a partir d\'aquest moment es podran incrustar a qualsevol web (incrustació pública). Només afecta pujades futures; els vídeos ja publicats no es modifiquen.';
$string['videoconnect:managevideos'] = 'Utilitzar el tauler de control de Video Connect (veure tots els vídeos, reintentar o descartar pujades)';
$string['whitelist_invalid'] = 'Domini no vàlid a la whitelist: {$a}. Un domini per línia, sense protocol (ex: campus.example.com).';
