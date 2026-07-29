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
$string['client_id'] = 'Client ID';
$string['client_secret'] = 'Client Secret';
$string['completed'] = 'This video will be available for viewing shortly';
$string['deleted'] = 'This course module has been deleted';
$string['discarded'] = 'This video has been discarded for a new one';
$string['error_folder'] = 'Error moving video to folder';
$string['error_uploading'] = 'An error occurred while uploading the video to Vimeo';
$string['error_whitelist'] = 'Error in update whitelist';
$string['filepath_not_found'] = 'No video selected for upload';
$string['folderid'] = 'Folder ID';
$string['fromdate'] = 'From';
$string['folderid_desc'] = "Numeric folder ID or pasted Vimeo folder URL (it will be stored as the numeric ID). Ex: https://vimeo.com/manage/folders/<strong>4206879</strong>";
$string['folderid_invalid'] = 'Enter a numeric Vimeo folder ID or a Vimeo folder URL (e.g. https://vimeo.com/manage/folders/4206879)';
$string['id_video_missing'] = 'ID video is missing in uploading response';
$string['idvideo'] = 'ID Video of Vimeo';
$string['idvideo_help'] = 'Numeric video ID or Vimeo video URL. Ex: https://vimeo.com/<strong>536287845</strong>';
$string['idvideo_invalid'] = 'Enter a numeric Vimeo video ID or a Vimeo video URL (e.g. https://vimeo.com/536287845)';
$string['is_authenticated'] = 'Is authenticated?';
$string['is_authenticated_desc'] = 'Select this option if you have a personal access token';
$string['missingidandcmid'] = 'Missing ID and CMID in URL';
$string['modulename'] = 'Video Connect';
$string['modulenameplural'] = 'Video Connects';
$string['not_executed'] = 'This video will be available for viewing shortly';
$string['pluginadministration'] = 'Video Connect Administration';
$string['pluginname'] = 'Video Connect';
$string['privacy:metadata'] = 'The Videoconnect resource plugin does not store any personal data.';
$string['scopes'] = 'Scopes';
$string['scopes_desc'] = 'Scopes accepted';
$string['scopes_not_exist'] = 'There are no scopes configured in the plugin';
$string['selectvideo'] = 'Select a video';
$string['task_upload_videos'] = 'Task to upload videos to Vimeo';
$string['uploading'] = 'This video will be available for viewing shortly';
$string['managesettings'] = 'Video Connect settings';
$string['manageuploads'] = 'Manage';
$string['backtopanel'] = 'Back to the control panel';
$string['cannotdiscard'] = 'The upload cannot be discarded: its current status does not allow it (an upload in progress cannot be discarded).';
$string['cannotretry_filemissing'] = 'The upload cannot be retried: the temporary file no longer exists. Upload the video again from the activity.';
$string['cannotretry_published'] = 'The upload cannot be retried: the activity already has a published video (retrying would duplicate it on Vimeo).';
$string['cannotretry_status'] = 'The upload cannot be retried: its current status does not allow it (it may have been processed or discarded meanwhile).';
$string['cronstale'] = 'There are pending uploads but the upload task has not run since {$a}. Check that the Moodle cron is running.';
$string['confirmdiscard'] = 'Do you want to discard this upload? It will not be processed.';
$string['confirmretry'] = 'Do you want to requeue this upload? The scheduled task will process it again.';
$string['discard'] = 'Discard';
$string['discarddone'] = 'The upload has been discarded.';
$string['eventuploaddiscarded'] = 'Upload attempt discarded';
$string['eventuploadretried'] = 'Upload attempt requeued';
$string['notrecoverable'] = 'Not recoverable: upload the video again from the activity';
$string['retry'] = 'Retry';
$string['retrydone'] = 'The upload has been queued again.';
$string['errordetail'] = 'Error detail';
$string['fileavailable'] = 'File available';
$string['filter_allcourses'] = 'All courses';
$string['filter_allstates'] = 'All states';
$string['noattempts'] = 'This activity has no upload attempts (embed-by-ID mode or no video yet).';
$string['todate'] = 'To';
$string['uploadattempts'] = 'Upload attempts';
$string['uploaddate'] = 'Upload date';
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
$string['panel'] = 'Video Connect control panel';
$string['state_error'] = 'Upload error';
$string['state_incident'] = 'Published with incident';
$string['state_novideo'] = 'No video';
$string['state_pending'] = 'Pending upload';
$string['state_published'] = 'Published';
$string['videostate'] = 'Video state';
$string['settingssaved'] = 'Video Connect settings saved';
$string['videoconnect:addinstance'] = 'Add a new video';
$string['videoconnect:configure'] = 'Configure the global Vimeo settings of Video Connect';
$string['videoconnect:managevideos'] = 'Use the Video Connect control panel (view all videos, retry or discard uploads)';
$string['videoconnect:view'] = 'View Videoconnect content.';
$string['videoconnectname'] = 'Name';
$string['videoconnectname_help'] = 'Select a name for this resource';
$string['vimeoheading'] = 'Vimeo API configuration';
$string['vimeoheadingdesc'] = 'Fill in the following fields with your Vimeo credentials';
$string['we_are_sorry'] = 'We are sorry';
$string['usewhitelist'] = 'Restrict embedding to whitelisted domains';
$string['usewhitelist_desc'] = '<strong>Warning:</strong> if disabled, videos uploaded from now on will be publicly embeddable on any website. Only affects future uploads; already published videos are not modified.';
$string['whitelist'] = 'Whitelist domains';
$string['whitelist_desc'] = "Domains allowed to embed the uploaded videos, one per line, without protocol ('http://' or 'https://'). Ex: campus.example.com";
$string['whitelist_invalid'] = 'Invalid domain in the whitelist: {$a}. One domain per line, without protocol (e.g. campus.example.com).';
