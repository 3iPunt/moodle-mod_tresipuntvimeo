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
$string['accesstoken_missing'] = 'The Personal Access Token mode is enabled but no token is configured. Review the plugin settings.';
$string['backtopanel'] = 'Back to the control panel';
$string['clientcredentials_failed'] = 'The video provider rejected the application credentials: {$a}';
$string['cannotdiscard'] = 'The upload cannot be discarded: its current status does not allow it (an upload in progress cannot be discarded).';
$string['cannotretry_filemissing'] = 'The upload cannot be retried: the temporary file no longer exists. Upload the video again from the activity.';
$string['cannotretry_published'] = 'The upload cannot be retried: the activity already has a published video (retrying would duplicate it on Vimeo).';
$string['cannotretry_status'] = 'The upload cannot be retried: its current status does not allow it (it may have been processed or discarded meanwhile).';
$string['card_error_mgmt_sub'] = 'Students see a notice asking them to contact their teacher. You can review the attempt from the control panel.';
$string['card_error_mgmt_title'] = 'The video could not be published';
$string['card_error_sub'] = 'If the problem persists, let your teacher know.';
$string['card_error_title'] = 'This video is not available right now';
$string['card_novideo_sub_student'] = 'Check back later.';
$string['card_novideo_sub_teacher'] = 'Edit the activity to upload a file or paste a video ID.';
$string['card_novideo_title'] = 'This activity has no video yet';
$string['card_pending_sub'] = 'It is being published on the video provider; you do not need to do anything. Come back in a few minutes.';
$string['card_pending_title'] = 'The video will be available shortly';
$string['cause'] = 'Cause';
$string['clearfilters'] = 'Clear filters';
$string['client_id'] = 'Client ID';
$string['client_secret'] = 'Client Secret';
$string['completed'] = 'This video will be available for viewing shortly';
$string['confirmdiscard'] = 'Do you want to discard this upload attempt? It will no longer be retried. This does not delete the video from the provider nor the activity.';
$string['confirmretry'] = 'Do you want to retry this upload? It will be processed on the next run of the scheduled task (2 minutes at most). The temporary file is kept.';
$string['coursescope'] = 'Course (scope)';
$string['cronstale'] = 'There are pending uploads but the upload task has not run since {$a}. Check that the Moodle cron is running.';
$string['cronstale_title'] = 'The cron seems stopped';
$string['deleted'] = 'This course module has been deleted';
$string['detail_title'] = 'Activity detail';
$string['diag_error_noid_body'] = 'The provider response did not include the video ID, so the upload did not complete. You can retry it from here.';
$string['diag_error_noid_title'] = 'The upload finished without a video ID';
$string['diag_error_norecover_body'] = 'The upload failed and the temporary file has already been purged. Upload the file again from the activity form.';
$string['diag_error_norecover_title'] = 'The upload is not recoverable from here';
$string['diag_error_retryable_body'] = 'The file upload did not complete. The temporary file is kept, so you can retry it from here.';
$string['diag_error_retryable_title'] = 'The upload failed, but you can retry it';
$string['diag_incident_body_folder'] = 'The video was uploaded but could not be moved to the configured folder: it stays in the provider root folder and plays normally. A provider admin can move it manually without affecting Moodle.';
$string['diag_incident_body_whitelist'] = 'The video was uploaded but the site domain could not be added to the whitelist, so the embedded playback is blocked. Add the domain manually from the provider administration or upload the video again.';
$string['diag_incident_title'] = 'The video was uploaded, but with an incident';
$string['diag_novideo_body'] = 'Upload a file or paste a provider video ID from the activity form.';
$string['diag_novideo_title'] = 'This activity has no video yet';
$string['diag_pending_queued_body'] = 'It will be processed on the next run of the scheduled task (every 2 minutes, requires an active cron).';
$string['diag_pending_queued_title'] = 'The upload is queued';
$string['diag_pending_uploading_body'] = 'The video will be available shortly. The provider may also take some time to transcode it.';
$string['diag_pending_uploading_title'] = 'Uploading the file to the provider';
$string['discard'] = 'Discard';
$string['displayinline'] = 'Show the video on the course page';
$string['displayinline_help'] = 'If enabled, the video player is embedded directly on the course page. If disabled, the course page shows the standard activity link and the video only plays inside the activity.';
$string['discarddone'] = 'The upload has been discarded.';
$string['discarded'] = 'This video has been discarded for a new one';
$string['emptyinitial_sub'] = 'When a teacher creates a Video Connect activity and uploads or links a video, it will appear here with its state.';
$string['emptyinitial_title'] = 'There are no Video Connect activities on this site yet';
$string['error_folder'] = 'Error moving video to folder';
$string['error_upload_interrupted'] = 'The upload was interrupted (the task crashed or timed out). You can retry it from the control panel.';
$string['error_uploading'] = 'An error occurred while uploading the video to Vimeo';
$string['error_whitelist'] = 'Error in update whitelist';
$string['error_whitelist_nodomains'] = 'The domain whitelist is enabled but no domains are configured. Review the plugin settings.';
$string['errordetail'] = 'Error detail';
$string['eventuploaddiscarded'] = 'Upload attempt discarded';
$string['eventuploadretried'] = 'Upload attempt requeued';
$string['fileavailable'] = 'File available';
$string['filepath_not_found'] = 'No video selected for upload';
$string['filter_allcourses'] = 'All courses';
$string['filter_allstates'] = 'All states';
$string['folderid'] = 'Folder ID';
$string['folderid_desc'] = "Numeric folder ID or pasted Vimeo folder URL (it will be stored as the numeric ID). Ex: https://vimeo.com/manage/folders/<strong>4206879</strong>";
$string['folderid_invalid'] = 'Enter a numeric Vimeo folder ID or a Vimeo folder URL (e.g. https://vimeo.com/manage/folders/4206879)';
$string['fromdate'] = 'From';
$string['id_video_missing'] = 'ID video is missing in uploading response';
$string['idvideo'] = 'ID Video of Vimeo';
$string['idvideo_help'] = 'Numeric video ID or Vimeo video URL. Ex: https://vimeo.com/<strong>536287845</strong>';
$string['idvideo_invalid'] = 'Enter a numeric Vimeo video ID or a Vimeo video URL (e.g. https://vimeo.com/536287845)';
$string['is_authenticated'] = 'Is authenticated?';
$string['is_authenticated_desc'] = 'Select this option if you have a personal access token';
$string['lastupload'] = 'Last upload';
$string['managesettings'] = 'Video Connect settings';
$string['manageuploads'] = 'Manage';
$string['missingidandcmid'] = 'Missing ID and CMID in URL';
$string['modulename'] = 'Video Connect';
$string['modulenameplural'] = 'Video Connects';
$string['noattempts'] = 'This activity has no upload attempts (embed-by-ID mode or no video yet).';
$string['noattempts_linked'] = 'The video is linked from an existing provider ID, so there are no upload attempts to show.';
$string['noresults_sub'] = 'No activity matches the current filters.';
$string['noresults_title'] = 'No results';
$string['not_executed'] = 'This video will be available for viewing shortly';
$string['notrecoverable'] = 'Not recoverable: upload the video again from the activity';
$string['notrecoverable_label'] = 'Not recoverable';
$string['novideoid'] = 'No video ID';
$string['panel'] = 'Video Connect control panel';
$string['panel_desc'] = 'Every video of the site with its publication state on the provider.';
$string['pluginadministration'] = 'Video Connect Administration';
$string['pluginname'] = 'Video Connect';
$string['privacy:metadata'] = 'The Videoconnect resource plugin does not store any personal data.';
$string['provideralt'] = 'Video provider: {$a}';
$string['providervideo'] = 'Video on the provider';
$string['retry'] = 'Retry';
$string['retrydone'] = 'The upload has been queued again.';
$string['scopes'] = 'Scopes';
$string['scopes_desc'] = 'Scopes requested to Vimeo. The full flow needs: <strong>public</strong> and <strong>private</strong> (playback), <strong>upload</strong> (upload videos; requires prior approval by Vimeo), <strong>edit</strong> (domain whitelist) and <strong>interact</strong> (move to folder).';
$string['scopes_not_exist'] = 'There are no scopes configured in the plugin';
$string['searchname_placeholder'] = 'Search by activity name…';
$string['selectvideo'] = 'Select a video';
$string['settingssaved'] = 'Video Connect settings saved';
$string['state_error'] = 'Upload error';
$string['state_incident'] = 'Published with incident';
$string['state_novideo'] = 'No video';
$string['state_pending'] = 'Pending upload';
$string['state_published'] = 'Published';
$string['task_upload_videos'] = 'Task to upload videos to Vimeo';
$string['testconnection'] = 'Test connection';
$string['testconnection_failed'] = 'The connection with the video provider failed: {$a}';
$string['testconnection_missingscopes'] = 'Connected as {$a->identity}, but the token is missing scopes required by the full flow: {$a->missing}. Uploads or their privacy steps will fail until the token is regenerated with them.';
$string['testconnection_ok'] = 'Connection OK — authenticated as {$a}, with every scope the full flow needs.';
$string['todate'] = 'To';
$string['updated'] = 'Updated';
$string['uploadattempts'] = 'Upload attempts';
$string['uploaddate'] = 'Upload date';
$string['uploading'] = 'This video will be available for viewing shortly';
$string['uploadstatus_0'] = 'No file selected';
$string['uploadstatus_1'] = 'Queued';
$string['uploadstatus_2'] = 'Discarded';
$string['uploadstatus_3'] = 'Uploading';
$string['uploadstatus_4'] = 'Upload failed';
$string['uploadstatus_5'] = 'Completed';
$string['uploadstatus_6'] = 'Activity deleted';
$string['uploadstatus_7'] = 'No video ID in the response';
$string['uploadstatus_8'] = 'Domain whitelist failed';
$string['uploadstatus_9'] = 'Folder move failed';
$string['usewhitelist'] = 'Restrict embedding to whitelisted domains';
$string['usewhitelist_desc'] = '<strong>Warning:</strong> if disabled, videos uploaded from now on will be publicly embeddable on any website. Only affects future uploads; already published videos are not modified.';
$string['video'] = 'Video';
$string['videoconnect:addinstance'] = 'Add a new video';
$string['videoconnect:configure'] = 'Configure the global Vimeo settings of Video Connect';
$string['videoconnect:managevideos'] = 'Use the Video Connect control panel (view all videos, retry or discard uploads)';
$string['videoconnect:view'] = 'View Videoconnect content.';
$string['videoconnectname'] = 'Name';
$string['videoconnectname_help'] = 'Select a name for this resource';
$string['videostate'] = 'Video state';
$string['viewdetail'] = 'View detail';
$string['viewdetailpanel'] = 'View detail in the control panel';
$string['vimeoheading'] = 'Vimeo API configuration';
$string['vimeoheadingdesc'] = 'Fill in the following fields with your Vimeo credentials';
$string['we_are_sorry'] = 'We are sorry';
$string['whitelist'] = 'Whitelist domains';
$string['whitelist_desc'] = "Domains allowed to embed the uploaded videos, one per line, without protocol ('http://' or 'https://'). Ex: campus.example.com";
$string['whitelist_invalid'] = 'Invalid domain in the whitelist: {$a}. One domain per line, without protocol (e.g. campus.example.com).';
