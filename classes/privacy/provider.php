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
 * Privacy Subsystem implementation for mod_videoconnect.
 *
 * @package     mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author      3IPUNT <contacte@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoconnect\privacy;

use context;
use core_privacy\local\metadata\collection;
use core_privacy\local\metadata\provider as metadata_provider;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\core_userlist_provider;
use core_privacy\local\request\plugin\provider as request_provider;
use core_privacy\local\request\userlist;

/**
 * Privacy Subsystem for mod_videoconnect.
 *
 * The plugin stores no personal data in its own tables (the upload queue
 * keeps file paths and provider responses, never user ids), but it acts as
 * a conduit: the video files picked by teachers — and the activity name,
 * used as the video title — are published to the external video provider
 * (Vimeo). That external location is declared here.
 *
 * @package     mod_videoconnect
 * @copyright   2021-2024 3ipunt {@link https://www.tresipunt.com}
 * @author      3IPUNT <contacte@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements metadata_provider, core_userlist_provider, request_provider {
    /**
     * Returns metadata about this plugin: data sent to the video provider.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        return $collection->add_external_location_link('vimeo', [
            'videofile' => 'privacy:metadata:vimeo:videofile',
            'name' => 'privacy:metadata:vimeo:name',
        ], 'privacy:metadata:vimeo');
    }

    /**
     * Contexts with user information: none is stored by this plugin.
     *
     * @param int $userid The user to search.
     * @return contextlist Empty contextlist.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        return new contextlist();
    }

    /**
     * Users with data within a context: none is stored by this plugin.
     *
     * @param userlist $userlist The userlist to add users to.
     */
    public static function get_users_in_context(userlist $userlist): void {
    }

    /**
     * Export user data: nothing to export, no user data is stored.
     *
     * @param approved_contextlist $contextlist Approved contexts to export.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
    }

    /**
     * Delete all data in a context: nothing to delete, no user data is stored.
     *
     * @param context $context The context to delete for.
     */
    public static function delete_data_for_all_users_in_context(context $context): void {
    }

    /**
     * Delete data for users: nothing to delete, no user data is stored.
     *
     * @param approved_userlist $userlist Approved users to delete for.
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
    }

    /**
     * Delete data for a user: nothing to delete, no user data is stored.
     *
     * @param approved_contextlist $contextlist Approved contexts to delete for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
    }
}
