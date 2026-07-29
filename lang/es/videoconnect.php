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
 * @author      3IPUNT <contacte@tresipunt.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['access_token'] = 'Personal Access Token';
$string['accesstoken_missing'] = 'El modo Personal Access Token está activado pero no hay ningún token configurado. Revisa la configuración del plugin.';
$string['backtopanel'] = 'Volver al panel de control';
$string['clientcredentials_failed'] = 'El proveedor de vídeo rechazó las credenciales de la aplicación: {$a}';
$string['cannotdiscard'] = 'No se puede descartar: su estado actual no lo permite (una subida en curso no se puede descartar).';
$string['cannotretry_filemissing'] = 'No se puede reintentar: el fichero temporal ya no existe. Vuelve a subir el vídeo desde la actividad.';
$string['cannotretry_published'] = 'No se puede reintentar: la actividad ya tiene un vídeo publicado (reintentar lo duplicaría en Vimeo).';
$string['cannotretry_status'] = 'No se puede reintentar: su estado actual no lo permite (puede que otra persona la haya procesado o descartado mientras tanto).';
$string['card_error_mgmt_sub'] = 'Los alumnos ven un aviso para que contacten con su profesor. Puedes revisar el intento desde el panel de control.';
$string['card_error_mgmt_title'] = 'El vídeo no se ha podido publicar';
$string['card_error_sub'] = 'Si el problema continúa, avisa a tu profesor.';
$string['card_error_title'] = 'Este vídeo no está disponible ahora mismo';
$string['card_novideo_sub_student'] = 'Vuelve a consultarla más tarde.';
$string['card_novideo_sub_teacher'] = 'Edita la actividad para subir un fichero o pegar un ID de vídeo.';
$string['card_novideo_title'] = 'Esta actividad aún no tiene vídeo';
$string['card_pending_sub'] = 'Se está publicando en el proveedor de vídeo; no hace falta que hagas nada. Vuelve a abrirla en unos minutos.';
$string['card_pending_title'] = 'El vídeo estará disponible en breve';
$string['cause'] = 'Causa';
$string['clearfilters'] = 'Quitar filtros';
$string['client_id'] = 'Client ID';
$string['client_secret'] = 'Client Secret';
$string['confirmdiscard'] = '¿Quieres descartar este intento de subida? Dejará de reintentarse. Esta acción no borra el vídeo del proveedor ni la actividad.';
$string['confirmretry'] = '¿Quieres reintentar esta subida? Se procesará en la próxima ejecución de la tarea programada (máx. 2 minutos). El fichero temporal se conserva.';
$string['coursescope'] = 'Curso (ámbito)';
$string['cronstale'] = 'Hay subidas pendientes pero la tarea de subida no se ejecuta desde {$a}. Comprueba que el cron de Moodle está activo.';
$string['cronstale_title'] = 'El cron parece detenido';
$string['detail_title'] = 'Detalle de la actividad';
$string['diag_error_noid_body'] = 'La respuesta del proveedor no incluyó el ID del vídeo, así que la subida no llegó a completarse. Puedes reintentarla desde aquí.';
$string['diag_error_noid_title'] = 'La subida terminó sin ID de vídeo';
$string['diag_error_norecover_body'] = 'La subida falló y el fichero temporal ya se ha purgado. Vuelve a subir el fichero desde el formulario de la actividad.';
$string['diag_error_norecover_title'] = 'La subida no es recuperable desde aquí';
$string['diag_error_retryable_body'] = 'La subida del fichero no se completó. El fichero temporal se conserva, así que puedes reintentarla desde aquí.';
$string['diag_error_retryable_title'] = 'La subida falló, pero puedes reintentarla';
$string['diag_incident_body_folder'] = 'El vídeo se subió pero no se pudo mover a la carpeta configurada: queda en la raíz del proveedor y se reproduce con normalidad. Un administrador del proveedor puede recolocarlo sin afectar a Moodle.';
$string['diag_incident_body_whitelist'] = 'El vídeo se subió pero el dominio del sitio no se pudo dar de alta en la whitelist, así que la reproducción embebida está bloqueada. Añade el dominio a mano desde la administración del proveedor o vuelve a subir el vídeo.';
$string['diag_incident_title'] = 'El vídeo se subió, pero con una incidencia';
$string['diag_novideo_body'] = 'Sube un fichero o pega un ID de vídeo del proveedor desde el formulario de la actividad.';
$string['diag_novideo_title'] = 'Esta actividad todavía no tiene vídeo';
$string['diag_pending_queued_body'] = 'Se procesará en la próxima ejecución de la tarea programada (cada 2 minutos, requiere cron activo).';
$string['diag_pending_queued_title'] = 'La subida está en cola';
$string['diag_pending_uploading_body'] = 'El vídeo estará disponible en breve. El proveedor puede tardar además en transcodificarlo.';
$string['diag_pending_uploading_title'] = 'Subiendo el fichero al proveedor';
$string['discard'] = 'Descartar';
$string['displayinline'] = 'Mostrar el vídeo en la página del curso';
$string['displayinline_help'] = 'Activado, el reproductor se embebe directamente en la página del curso. Desactivado, el curso muestra el enlace estándar a la actividad y el vídeo solo se reproduce dentro de ella.';
$string['discarddone'] = 'La subida se ha descartado.';
$string['emptyinitial_sub'] = 'Cuando un profesor cree una actividad Video Connect y suba o enlace un vídeo, aparecerá aquí con su estado.';
$string['emptyinitial_title'] = 'Aún no hay actividades Video Connect en el sitio';
$string['error_upload_interrupted'] = 'La subida se interrumpió (la tarea murió o agotó el tiempo). Puedes reintentarla desde el panel de control.';
$string['error_whitelist_nodomains'] = 'La whitelist de dominios está activada pero no hay dominios configurados. Revisa la configuración del plugin.';
$string['errordetail'] = 'Detalle del error';
$string['eventuploaddiscarded'] = 'Intento de subida descartado';
$string['eventuploadretried'] = 'Intento de subida re-encolado';
$string['fileavailable'] = 'Fichero disponible';
$string['filter_allcourses'] = 'Todos los cursos';
$string['filter_allstates'] = 'Todos los estados';
$string['folderid'] = 'Folder ID';
$string['folderid_desc'] = "ID numérico de la carpeta o URL de la carpeta de Vimeo pegada tal cual (se guardará como ID numérico). Ej: https://vimeo.com/manage/folders/<strong>4206879</strong>";
$string['folderid_invalid'] = 'Introduce un ID numérico de carpeta de Vimeo o una URL de carpeta de Vimeo (ej: https://vimeo.com/manage/folders/4206879)';
$string['fromdate'] = 'Desde';
$string['idvideo'] = 'ID del vídeo de Vimeo';
$string['idvideo_help'] = 'ID numérico del vídeo o URL del vídeo de Vimeo. Ej: https://vimeo.com/<strong>536287845</strong>';
$string['idvideo_invalid'] = 'Introduce un ID numérico de vídeo de Vimeo o una URL de vídeo de Vimeo (ej: https://vimeo.com/536287845)';
$string['is_authenticated'] = 'Utiliza autenticación';
$string['is_authenticated_desc'] = 'Seleccione esta opción si dispone de un personal access token';
$string['lastupload'] = 'Última subida';
$string['managesettings'] = 'Configuración de Video Connect';
$string['missingidandcmid'] = 'No se encuentra el ID o el CMID en la URL';
$string['modulename'] = 'Video Connect';
$string['modulenameplural'] = 'Video Connects';
$string['noattempts'] = 'Esta actividad no tiene intentos de subida (modo "ID existente" o sin vídeo todavía).';
$string['noattempts_linked'] = 'El vídeo está enlazado desde un ID existente del proveedor, así que no hay intentos de subida que mostrar.';
$string['noresults_sub'] = 'Ninguna actividad coincide con los filtros actuales.';
$string['noresults_title'] = 'Sin resultados';
$string['notrecoverable'] = 'No recuperable: vuelve a subir el vídeo desde la actividad';
$string['notrecoverable_label'] = 'No recuperable';
$string['novideoid'] = 'Sin ID de vídeo';
$string['panel'] = 'Panel de control de Video Connect';
$string['panel_desc'] = 'Todos los vídeos del sitio con su estado de publicación en el proveedor.';
$string['pluginadministration'] = 'Administración Video Connect';
$string['pluginname'] = 'Video Connect';
$string['privacy:metadata'] = 'El plugin Videoconnect no almacena ningún dato personal.';
$string['provideralt'] = 'Proveedor de vídeo: {$a}';
$string['providervideo'] = 'Vídeo en el proveedor';
$string['retry'] = 'Reintentar';
$string['retrydone'] = 'La subida se ha vuelto a encolar.';
$string['scopes'] = 'Scopes';
$string['scopes_desc'] = 'Scopes solicitados a Vimeo. El flujo completo necesita: <strong>public</strong> y <strong>private</strong> (reproducción), <strong>upload</strong> (subir vídeos; requiere aprobación previa de Vimeo), <strong>edit</strong> (whitelist de dominios) e <strong>interact</strong> (mover a carpeta).';
$string['scopes_not_exist'] = 'No existen scopes configurados en el plugin';
$string['searchname_placeholder'] = 'Buscar por nombre de actividad…';
$string['selectvideo'] = 'Selecciona un vídeo';
$string['settingssaved'] = 'Configuración de Video Connect guardada';
$string['state_error'] = 'Error de subida';
$string['state_incident'] = 'Publicado con incidencia';
$string['state_novideo'] = 'Sin vídeo';
$string['state_pending'] = 'Pendiente de subir';
$string['state_published'] = 'Publicado';
$string['task_upload_videos'] = 'Tarea para subir vídeos a Vimeo';
$string['testconnection'] = 'Probar conexión';
$string['testconnection_failed'] = 'La conexión con el proveedor de vídeo falló: {$a}';
$string['testconnection_missingscopes'] = 'Conectado como {$a->identity}, pero al token le faltan scopes que el flujo completo necesita: {$a->missing}. Las subidas o sus pasos de privacidad fallarán hasta regenerar el token con ellos.';
$string['testconnection_ok'] = 'Conexión correcta — autenticado como {$a}, con todos los scopes que necesita el flujo completo.';
$string['todate'] = 'Hasta';
$string['updated'] = 'Actualizado';
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
$string['usewhitelist'] = 'Restringir el embebido a los dominios de la whitelist';
$string['usewhitelist_desc'] = '<strong>Aviso:</strong> si se desactiva, los vídeos subidos a partir de ese momento podrán embeberse en cualquier web (embebido público). Solo afecta a subidas futuras; los vídeos ya publicados no se modifican.';
$string['video'] = 'Vídeo';
$string['videoconnect:addinstance'] = 'Añadir nuevo vídeo';
$string['videoconnect:configure'] = 'Configurar los ajustes globales de Vimeo de Video Connect';
$string['videoconnect:managevideos'] = 'Usar el panel de control de Video Connect (ver todos los vídeos, reintentar o descartar subidas)';
$string['videoconnect:view'] = 'Visualizar un contenido de Videoconnect.';
$string['videoconnectname'] = 'Nombre del módulo';
$string['videoconnectname_help'] = 'Seleccione un nombre para este recurso';
$string['videostate'] = 'Estado del vídeo';
$string['viewdetail'] = 'Ver detalle';
$string['viewdetailpanel'] = 'Ver detalle en el panel de control';
$string['vimeoheading'] = 'Vimeo API configuración';
$string['vimeoheadingdesc'] = 'Rellene los siguientes campos con sus credenciales de Vimeo';
$string['whitelist'] = 'Dominios de la whitelist';
$string['whitelist_desc'] = "Dominios autorizados a embeber los vídeos subidos, uno por línea, sin protocolo ('http://' o 'https://'). Ej: campus.example.com";
$string['whitelist_invalid'] = 'Dominio no válido en la whitelist: {$a}. Un dominio por línea, sin protocolo (ej: campus.example.com).';
