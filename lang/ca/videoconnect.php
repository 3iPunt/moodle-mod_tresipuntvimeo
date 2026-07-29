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
 * @package     mod_videoconnect
 * @category    string
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author      3IPUNT <contacte@tresipunt.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['access_token'] = 'Personal Access Token';
$string['accesstoken_missing'] = 'El mode Personal Access Token està activat però no hi ha cap token configurat. Revisa la configuració del plugin.';
$string['backtopanel'] = 'Tornar al tauler de control';
$string['clientcredentials_failed'] = 'El proveïdor de vídeo va rebutjar les credencials de l\'aplicació: {$a}';
$string['cannotdiscard'] = 'No es pot descartar: el seu estat actual no ho permet (una pujada en curs no es pot descartar).';
$string['cannotretry_filemissing'] = 'No es pot reintentar: el fitxer temporal ja no existeix. Torna a pujar el vídeo des de l\'activitat.';
$string['cannotretry_published'] = 'No es pot reintentar: l\'activitat ja té un vídeo publicat (reintentar el duplicaria a Vimeo).';
$string['cannotretry_status'] = 'No es pot reintentar: el seu estat actual no ho permet (potser una altra persona l\'ha processat o descartat mentrestant).';
$string['card_error_mgmt_sub'] = 'Els alumnes veuen un avís perquè contactin amb el seu professor. Pots revisar l\'intent des del tauler de control.';
$string['card_error_mgmt_title'] = 'El vídeo no s\'ha pogut publicar';
$string['card_error_sub'] = 'Si el problema continua, avisa el teu professor.';
$string['card_error_title'] = 'Aquest vídeo no està disponible ara mateix';
$string['card_novideo_sub_student'] = 'Torna a consultar-la més tard.';
$string['card_novideo_sub_teacher'] = 'Edita l\'activitat per pujar un fitxer o enganxar un ID de vídeo.';
$string['card_novideo_title'] = 'Aquesta activitat encara no té vídeo';
$string['card_pending_sub'] = 'S\'està publicant al proveïdor de vídeo; no cal que facis res. Torna a obrir-la d\'aquí a uns minuts.';
$string['card_pending_title'] = 'El vídeo estarà disponible en breu';
$string['cause'] = 'Causa';
$string['clearfilters'] = 'Treure filtres';
$string['client_id'] = 'Client ID';
$string['client_secret'] = 'Client Secret';
$string['confirmdiscard'] = 'Vols descartar aquest intent de pujada? Deixarà de reintentar-se. Aquesta acció no esborra el vídeo del proveïdor ni l\'activitat.';
$string['confirmretry'] = 'Vols reintentar aquesta pujada? Es processarà a la propera execució de la tasca programada (màx. 2 minuts). El fitxer temporal es conserva.';
$string['coursescope'] = 'Curs (àmbit)';
$string['cronstale'] = 'Hi ha pujades pendents però la tasca de pujada no s\'executa des de {$a}. Comprova que el cron de Moodle està actiu.';
$string['cronstale_title'] = 'El cron sembla aturat';
$string['detail_title'] = 'Detall de l\'activitat';
$string['diag_error_noid_body'] = 'La resposta del proveïdor no va incloure l\'ID del vídeo, així que la pujada no es va completar. Pots reintentar-la des d\'aquí.';
$string['diag_error_noid_title'] = 'La pujada va acabar sense ID de vídeo';
$string['diag_error_norecover_body'] = 'La pujada va fallar i el fitxer temporal ja s\'ha purgat. Torna a pujar el fitxer des del formulari de l\'activitat.';
$string['diag_error_norecover_title'] = 'La pujada no és recuperable des d\'aquí';
$string['diag_error_retryable_body'] = 'La pujada del fitxer no es va completar. El fitxer temporal es conserva, així que pots reintentar-la des d\'aquí.';
$string['diag_error_retryable_title'] = 'La pujada va fallar, però pots reintentar-la';
$string['diag_incident_body_folder'] = 'El vídeo es va pujar però no es va poder moure a la carpeta configurada: queda a l\'arrel del proveïdor i es reprodueix amb normalitat. Un administrador del proveïdor el pot recol·locar sense afectar Moodle.';
$string['diag_incident_body_whitelist'] = 'El vídeo es va pujar però el domini del lloc no es va poder donar d\'alta a la whitelist, així que la reproducció incrustada està bloquejada. Afegeix el domini manualment des de l\'administració del proveïdor o torna a pujar el vídeo.';
$string['diag_incident_title'] = 'El vídeo es va pujar, però amb una incidència';
$string['diag_novideo_body'] = 'Puja un fitxer o enganxa un ID de vídeo del proveïdor des del formulari de l\'activitat.';
$string['diag_novideo_title'] = 'Aquesta activitat encara no té vídeo';
$string['diag_pending_queued_body'] = 'Es processarà a la propera execució de la tasca programada (cada 2 minuts, requereix cron actiu).';
$string['diag_pending_queued_title'] = 'La pujada està en cua';
$string['diag_pending_uploading_body'] = 'El vídeo estarà disponible en breu. El proveïdor pot trigar a més a transcodificar-lo.';
$string['diag_pending_uploading_title'] = 'Pujant el fitxer al proveïdor';
$string['discard'] = 'Descartar';
$string['displayinline'] = 'Mostrar el vídeo a la pàgina del curs';
$string['displayinline_help'] = 'Activat, el reproductor s\'incrusta directament a la pàgina del curs. Desactivat, el curs mostra l\'enllaç estàndard a l\'activitat i el vídeo només es reprodueix dins d\'ella.';
$string['discarddone'] = 'La pujada s\'ha descartat.';
$string['emptyinitial_sub'] = 'Quan un professor creï una activitat Video Connect i pugi o enllaci un vídeo, apareixerà aquí amb el seu estat.';
$string['emptyinitial_title'] = 'Encara no hi ha activitats Video Connect al lloc';
$string['error_upload_interrupted'] = 'La pujada es va interrompre (la tasca va morir o va esgotar el temps). Pots reintentar-la des del tauler de control.';
$string['error_whitelist_nodomains'] = 'La whitelist de dominis està activada però no hi ha dominis configurats. Revisa la configuració del plugin.';
$string['errordetail'] = 'Detall de l\'error';
$string['eventuploaddiscarded'] = 'Intent de pujada descartat';
$string['eventuploadretried'] = 'Intent de pujada tornat a encuar';
$string['fileavailable'] = 'Fitxer disponible';
$string['filter_allcourses'] = 'Tots els cursos';
$string['filter_allstates'] = 'Tots els estats';
$string['folderid'] = 'Folder ID';
$string['folderid_desc'] = "ID numèric de la carpeta o URL de la carpeta de Vimeo enganxada tal qual (es desarà com a ID numèric). Ex: https://vimeo.com/manage/folders/<strong>4206879</strong>";
$string['folderid_invalid'] = 'Introdueix un ID numèric de carpeta de Vimeo o una URL de carpeta de Vimeo (ex: https://vimeo.com/manage/folders/4206879)';
$string['fromdate'] = 'Des de';
$string['idvideo'] = 'ID del vídeo de Vimeo';
$string['idvideo_help'] = 'ID numèric del vídeo o URL del vídeo de Vimeo. Ex: https://vimeo.com/<strong>536287845</strong>';
$string['idvideo_invalid'] = 'Introdueix un ID numèric de vídeo de Vimeo o una URL de vídeo de Vimeo (ex: https://vimeo.com/536287845)';
$string['is_authenticated'] = 'Utilitza autenticació';
$string['is_authenticated_desc'] = 'Selecciona aquesta opció si disposes d\'un personal access token';
$string['lastupload'] = 'Darrera pujada';
$string['managesettings'] = 'Configuració de Video Connect';
$string['missingidandcmid'] = 'No es troba l\'ID o el CMID a la URL';
$string['modulename'] = 'Video Connect';
$string['modulenameplural'] = 'Video Connects';
$string['noattempts'] = 'Aquesta activitat no té intents de pujada (mode "ID existent" o sense vídeo encara).';
$string['noattempts_linked'] = 'El vídeo està enllaçat des d\'un ID existent del proveïdor, així que no hi ha intents de pujada per mostrar.';
$string['noresults_sub'] = 'Cap activitat coincideix amb els filtres actuals.';
$string['noresults_title'] = 'Sense resultats';
$string['notrecoverable'] = 'No recuperable: torna a pujar el vídeo des de l\'activitat';
$string['notrecoverable_label'] = 'No recuperable';
$string['novideoid'] = 'Sense ID de vídeo';
$string['panel'] = 'Tauler de control de Video Connect';
$string['panel_desc'] = 'Tots els vídeos del lloc amb el seu estat de publicació al proveïdor.';
$string['pluginadministration'] = 'Administració de Video Connect';
$string['pluginname'] = 'Video Connect';
$string['privacy:metadata:vimeo'] = 'Video Connect publica els vídeos pujats al compte de Vimeo configurat pel lloc.';
$string['privacy:metadata:vimeo:name'] = 'El nom de l\'activitat, usat com a títol del vídeo a Vimeo.';
$string['privacy:metadata:vimeo:videofile'] = 'El fitxer de vídeo seleccionat pel professor, que es puja a Vimeo i pot contenir dades personals (imatges, veus).';
$string['provideralt'] = 'Proveïdor de vídeo: {$a}';
$string['providervideo'] = 'Vídeo al proveïdor';
$string['retry'] = 'Reintentar';
$string['retrydone'] = 'La pujada s\'ha tornat a encuar.';
$string['scopes'] = 'Scopes';
$string['scopes_desc'] = 'Scopes sol·licitats a Vimeo. El flux complet necessita: <strong>public</strong> i <strong>private</strong> (reproducció), <strong>upload</strong> (pujar vídeos; requereix aprovació prèvia de Vimeo), <strong>edit</strong> (whitelist de dominis) i <strong>interact</strong> (moure a carpeta).';
$string['scopes_not_exist'] = 'No hi ha scopes configurats al plugin';
$string['searchname_placeholder'] = 'Cercar per nom d\'activitat…';
$string['selectvideo'] = 'Selecciona un vídeo';
$string['settingssaved'] = 'Configuració de Video Connect desada';
$string['state_error'] = 'Error de pujada';
$string['state_incident'] = 'Publicat amb incidència';
$string['state_novideo'] = 'Sense vídeo';
$string['state_pending'] = 'Pendent de pujar';
$string['state_published'] = 'Publicat';
$string['task_upload_videos'] = 'Tasca per pujar vídeos a Vimeo';
$string['testconnection'] = 'Provar la connexió';
$string['testconnection_failed'] = 'La connexió amb el proveïdor de vídeo va fallar: {$a}';
$string['testconnection_missingscopes'] = 'Connectat com a {$a->identity}, però al token li falten scopes que el flux complet necessita: {$a->missing}. Les pujades o els seus passos de privacitat fallaran fins que es regeneri el token amb ells.';
$string['testconnection_ok'] = 'Connexió correcta — autenticat com a {$a}, amb tots els scopes que necessita el flux complet.';
$string['todate'] = 'Fins a';
$string['updated'] = 'Actualitzat';
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
$string['usewhitelist'] = 'Restringir la incrustació als dominis de la whitelist';
$string['usewhitelist_desc'] = '<strong>Avís:</strong> si es desactiva, els vídeos pujats a partir d\'aquest moment es podran incrustar a qualsevol web (incrustació pública). Només afecta pujades futures; els vídeos ja publicats no es modifiquen.';
$string['video'] = 'Vídeo';
$string['videoconnect:addinstance'] = 'Afegir un vídeo nou';
$string['videoconnect:configure'] = 'Configurar els ajustos globals de Vimeo de Video Connect';
$string['videoconnect:managevideos'] = 'Utilitzar el tauler de control de Video Connect (veure tots els vídeos, reintentar o descartar pujades)';
$string['videoconnect:view'] = 'Visualitzar un contingut de Videoconnect.';
$string['videoconnectname'] = 'Nom del mòdul';
$string['videoconnectname_help'] = 'Selecciona un nom per a aquest recurs';
$string['videostate'] = 'Estat del vídeo';
$string['viewdetail'] = 'Veure detall';
$string['viewdetailpanel'] = 'Veure detall al tauler de control';
$string['vimeoheading'] = 'Configuració de l\'API de Vimeo';
$string['vimeoheadingdesc'] = 'Omple els camps següents amb les teves credencials de Vimeo';
$string['whitelist'] = 'Dominis de la whitelist';
$string['whitelist_desc'] = "Dominis autoritzats a incrustar els vídeos pujats, un per línia, sense protocol ('http://' o 'https://'). Ex: campus.example.com";
$string['whitelist_invalid'] = 'Domini no vàlid a la whitelist: {$a}. Un domini per línia, sense protocol (ex: campus.example.com).';
