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
 * Privacy Subsystem implementation for block_user_favorites.
 *
 * @package    block_user_favorites
 * @copyright  26-10-2018 MFreak.nl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_user_favorites\privacy;

use block_user_favorites\favorites;
use coding_exception;
use context;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\core_userlist_provider;
use core_privacy\local\request\user_preference_provider;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy Subsystem for block_user_favorites.
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  26-10-2018 MFreak.nl
 */
class provider implements

    // The block_html block stores user provided data.
    \core_privacy\local\metadata\provider,

    // This plugin is capable of determining which users have data within it.
    core_userlist_provider,

    // The block_user_favorites provides data directly to core.
    \core_privacy\local\request\plugin\provider,

    // Previous version of this block used user_preference.
    user_preference_provider {

    /**
     * Returns meta-data information about the block_user_favorites.
     *
     * @param collection $collection A collection of meta-data.
     *
     * @return collection Return the collection of meta-data.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_user_preference('user_favorites', 'privacy:metadata:links');
        $collection->add_database_table('block_user_favorites', [
            'url' => 'privacy:metadata:favorite:url',
            'hash' => 'privacy:metadata:favorite:hash',
            'title' => 'privacy:metadata:favorite:title',
            'created_at' => 'privacy:metadata:favorite:created_at',
        ], 'privacy:metadata:favorite');

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     *
     * @return  contextlist   $contextlist  The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {

        // Is at the user context.
        $contextlist = new contextlist();
        $sql = "SELECT c.id
                  FROM {block_user_favorites} f
                  JOIN {context} c ON c.instanceid = f.userid AND c.contextlevel = :contextuser
                 WHERE f.userid = :userid
              GROUP BY c.id";

        $params = [
            'contextuser' => CONTEXT_USER,
            'userid' => $userid,
        ];

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin
     *                           combination.
     */
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();

        if (!$context instanceof \context_user) {
            return;
        }

        $params = [
            'contextid' => $context->id,
            'contextuser' => CONTEXT_USER,
        ];

        $sql = "SELECT f.userid as userid
                  FROM {block_user_favorites} f
                  JOIN {context} ctx
                       ON ctx.instanceid = f.userid
                       AND ctx.contextlevel = :contextuser
                 WHERE ctx.id = :contextid";

        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     *
     * @throws \dml_exception
     * @throws coding_exception
     */
    public static function export_user_data(approved_contextlist $contextlist): void {

        // If the user has block_community data, then only the User context should be present so get the first context.
        $contexts = $contextlist->get_contexts();
        if (count($contexts) == 0) {
            return;
        }
        $context = reset($contexts);

        // Sanity check that context is at the User context level, then get the userid.
        if ($context->contextlevel !== CONTEXT_USER) {
            return;
        }

        $subcontext = [
            get_string('pluginname', 'block_user_favorites'),
            get_string('user_favorites:view', 'block_user_favorites'),
        ];

        // Stored on user context.
        $user = $contextlist->get_user();
        writer::with_context($context)->export_data($subcontext,
            (object) ['links' => self::get_urls($user->id)]);
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param context $context The specific context to delete data for.
     *
     * @throws \dml_exception
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;

        // Sanity check that context is at the User context level, then get the userid.
        if ($context->contextlevel !== CONTEXT_USER) {
            return;
        }
        $userid = $context->instanceid;

        $DB->delete_records('block_user_favorites', ['userid' => $userid]);
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     *
     * @throws \dml_exception
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();

        if ($context instanceof \context_user) {
            $DB->delete_records('block_user_favorites', ['userid' => $context->instanceid]);
        }
    }

    /**
     * Delete all user data for the specified user.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     *
     * @throws \dml_exception
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        // If the user has block_community data, then only the User context should be present so get the first context.
        $contexts = $contextlist->get_contexts();
        if (count($contexts) == 0) {
            return;
        }
        $context = reset($contexts);

        // Sanity check that context is at the User context level, then get the userid.
        if ($context->contextlevel !== CONTEXT_USER) {
            return;
        }
        $userid = $context->instanceid;
        $DB->delete_records('block_user_favorites', ['userid' => $userid]);
    }

    /**
     * Export all user preferences for the user_favorites block
     *
     * @param int $userid The userid of the user whose data is to be exported.
     *
     * @throws coding_exception
     */
    public static function export_user_preferences(int $userid): void {
        $preference = get_user_preferences('user_favorites', null, $userid);
        if (isset($preference)) {
            writer::export_user_preference('user_favorites', '',
                $preference, get_string('privacy:metadata:links', 'block_user_favorites'));
        }
    }

    /**
     * get_urls
     *
     * @param int $userid
     *
     * @return array|mixed
     * @throws \dml_exception
     * @throws coding_exception
     */
    private static function get_urls($userid) {
        $favorites = new favorites($userid);

        // Todo convert timestamp to readable format.
        return $favorites->get_all();
    }

}
