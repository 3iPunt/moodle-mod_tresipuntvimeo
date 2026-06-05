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
 * Plugin strings are defined here.
 *
 * @package     mod_videoconnect
 * @category    string
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['access_token'] = 'Personal Access Token';
$string['client_id'] = 'Client ID';
$string['client_secret'] = 'Client Secret';
$string['completed'] = 'Este vídeo estará disponible para verlo dentro de poco';
$string['deleted'] = 'Este course module ha sido borrado';
$string['discarded'] = 'Este vídeo ha sido descartado por uno nuevo';
$string['error_folder'] = 'Error al mover el video a la carpeta';
$string['error_uploading'] = 'Ha ocurrido un error al subir el vídeo a Vimeo';
$string['error_whitelist'] = 'No se ha actualizado correctamente el whitelist de la privacidad';
$string['filepath_not_found'] = 'No se ha seleccionado ningún vídeo para subir';
$string['folderid'] = 'Folder ID';
$string['folderid_desc'] = "ID numérico de la carpeta o URL de la carpeta de Vimeo pegada tal cual (se guardará como ID numérico). Ej: https://vimeo.com/manage/folders/<strong>4206879</strong>";
$string['folderid_invalid'] = 'Introduce un ID numérico de carpeta de Vimeo o una URL de carpeta de Vimeo (ej: https://vimeo.com/manage/folders/4206879)';
$string['fromdate'] = 'Desde';
$string['id_video_missing'] = 'No se ha podido recuperar el ID del video en la respuesta de Vimeo';
$string['idvideo'] = 'ID del vídeo de Vimeo';
$string['idvideo_help'] = 'ID numérico del vídeo o URL del vídeo de Vimeo. Ej: https://vimeo.com/<strong>536287845</strong>';
$string['idvideo_invalid'] = 'Introduce un ID numérico de vídeo de Vimeo o una URL de vídeo de Vimeo (ej: https://vimeo.com/536287845)';
$string['is_authenticated'] = 'Utiliza autenticación';
$string['is_authenticated_desc'] = 'Seleccione esta opción si dispone de un personal access token';
$string['missingidandcmid'] = 'No se encuentra el ID o el CMID en la URL';
$string['modulename'] = 'Video Connect';
$string['modulenameplural'] = 'Video Connects';
$string['not_executed'] = 'Este vídeo estará disponible para verlo dentro de poco';
$string['pluginadministration'] = 'Administración Video Connect';
$string['pluginname'] = 'Video Connect';
$string['privacy:metadata'] = 'El plugin Videoconnect no almacena ningún dato personal.';
$string['scopes'] = 'Scopes';
$string['scopes_desc'] = 'Alcances aceptados';
$string['scopes_not_exist'] = 'No existen scopes configurados en el plugin';
$string['selectvideo'] = 'Selecciona un vídeo';
$string['task_upload_videos'] = 'Tarea para subir vídeos a Vimeo';
$string['uploading'] = 'Este vídeo estará disponible para verlo dentro de poco';
$string['managesettings'] = 'Configuración de Video Connect';
$string['manageuploads'] = 'Gestionar';
$string['backtopanel'] = 'Volver al panel de control';
$string['cannotdiscard'] = 'No se puede descartar: su estado actual no lo permite (una subida en curso no se puede descartar).';
$string['cannotretry_filemissing'] = 'No se puede reintentar: el fichero temporal ya no existe. Vuelve a subir el vídeo desde la actividad.';
$string['cannotretry_published'] = 'No se puede reintentar: la actividad ya tiene un vídeo publicado (reintentar lo duplicaría en Vimeo).';
$string['cannotretry_status'] = 'No se puede reintentar: su estado actual no lo permite (puede que otra persona la haya procesado o descartado mientras tanto).';
$string['cronstale'] = 'Hay subidas pendientes pero la tarea de subida no se ejecuta desde {$a}. Comprueba que el cron de Moodle está activo.';
$string['confirmdiscard'] = '¿Quieres descartar esta subida? No se procesará.';
$string['confirmretry'] = '¿Quieres re-encolar esta subida? La tarea programada volverá a procesarla.';
$string['discard'] = 'Descartar';
$string['discarddone'] = 'La subida se ha descartado.';
$string['eventuploaddiscarded'] = 'Intento de subida descartado';
$string['eventuploadretried'] = 'Intento de subida re-encolado';
$string['notrecoverable'] = 'No recuperable: vuelve a subir el vídeo desde la actividad';
$string['retry'] = 'Reintentar';
$string['retrydone'] = 'La subida se ha vuelto a encolar.';
$string['errordetail'] = 'Detalle del error';
$string['fileavailable'] = 'Fichero disponible';
$string['filter_allcourses'] = 'Todos los cursos';
$string['filter_allstates'] = 'Todos los estados';
$string['noattempts'] = 'Esta actividad no tiene intentos de subida (modo "ID existente" o sin vídeo todavía).';
$string['todate'] = 'Hasta';
$string['uploadattempts'] = 'Intentos de subida';
$string['uploaddate'] = 'Fecha de subida';
$string['uploadstatus_0'] = 'Sin fichero seleccionado';
$string['uploadstatus_1'] = 'En cola';
$string['uploadstatus_2'] = 'Descartada';
$string['uploadstatus_3'] = 'Subiendo';
$string['uploadstatus_4'] = 'Subida fallida';
$string['uploadstatus_5'] = 'Completada';
$string['uploadstatus_6'] = 'Actividad eliminada';
$string['uploadstatus_7'] = 'Respuesta sin ID de vídeo';
$string['uploadstatus_8'] = 'Falló la whitelist de dominio';
$string['uploadstatus_9'] = 'Falló el movimiento a carpeta';
$string['panel'] = 'Panel de control de Video Connect';
$string['state_error'] = 'Error de subida';
$string['state_incident'] = 'Publicado con incidencia';
$string['state_novideo'] = 'Sin vídeo';
$string['state_pending'] = 'Pendiente de subir';
$string['state_published'] = 'Publicado';
$string['videostate'] = 'Estado del vídeo';
$string['settingssaved'] = 'Configuración de Video Connect guardada';
$string['videoconnect:addinstance'] = 'Añadir nuevo vídeo';
$string['videoconnect:configure'] = 'Configurar los ajustes globales de Vimeo de Video Connect';
$string['videoconnect:managevideos'] = 'Usar el panel de control de Video Connect (ver todos los vídeos, reintentar o descartar subidas)';
$string['videoconnect:view'] = 'Visualizar un contenido de Videoconnect.';
$string['videoconnectname'] = 'Nombre del módulo';
$string['videoconnectname_help'] = 'Seleccione un nombre para este recurso';
$string['vimeoheading'] = 'Vimeo API configuración';
$string['vimeoheadingdesc'] = 'Rellene los siguientes campos con sus credenciales de Vimeo';
$string['we_are_sorry'] = 'Lo sentimos';
$string['usewhitelist'] = 'Restringir el embebido a los dominios de la whitelist';
$string['usewhitelist_desc'] = '<strong>Aviso:</strong> si se desactiva, los vídeos subidos a partir de ese momento podrán embeberse en cualquier web (embebido público). Solo afecta a subidas futuras; los vídeos ya publicados no se modifican.';
$string['whitelist'] = 'Dominios de la whitelist';
$string['whitelist_desc'] = "Dominios autorizados a embeber los vídeos subidos, uno por línea, sin protocolo ('http://' o 'https://'). Ej: campus.example.com";
$string['whitelist_invalid'] = 'Dominio no válido en la whitelist: {$a}. Un dominio por línea, sin protocolo (ej: campus.example.com).';
