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

namespace local_faultreporting\privacy;

use core_privacy\local\request\userlist;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\deletion_criteria;
use core_privacy\local\request\writer;
use core_privacy\local\request\helper as request_helper;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\transform;
use tool_dataprivacy\context_instance;
use core_privacy\local\request\contextlist;

/**
 * Privacy Subsystem for local_faultreporting.
 *
 * @package    local_faultreporting
 * @copyright  2025 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    // This plugin has data.
    \core_privacy\local\metadata\provider,

    // This plugin currently implements the original plugin\provider interface.
    \core_privacy\local\request\plugin\provider,

    // This plugin is capable of determining which users have data within it.
    \core_privacy\local\request\core_userlist_provider {

    /**
     * Return the fields which contain personal data.
     *
     * @param collection $collection a reference to the collection to use to store the metadata.
     * @return collection the updated collection of metadata items.
     */
    public static function get_metadata(collection $collection): collection {
        // The 'local_faultreport' table stores information about individual fault reports.
        $collection->add_database_table(
            'local_faultreporting',
            [
                'userid' => 'privacy:metadata:local_faultreporting:userid',
                'description' => 'privacy:metadata:local_faultreporting:description',
            ],
            'privacy:metadata:local_faultreporting'
        );

        // Data in the 'local_faultreport' table is exported to an external location i.e., Assyst.
        $collection->add_external_location_link(
            'assystapi',
            [
                'userid' => 'privacy:metadata:local_faultreporting:userid',
                'description' => 'privacy:metadata:local_faultreporting:description',
            ],
            'privacy:metadata:local_faultreporting'
        );

        return $collection;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param   userlist    $userlist   The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!is_a($context, \context_system::class)) {
            return;
        }

        // Get the list of users who have data in this context.

        $reports = \local_faultreporting\faultreport::get_reports();
        foreach ($reports as $report) {
            // Note that the add_user function convieniently handles duplicates.
            $userlist->add_user($report->userid);
        }
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param   int           $userid       The user to search.
     * @return  contextlist   $contextlist  The list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $reports = \local_faultreporting\faultreport::get_reports_by_user($userid);

        if (count($reports)) {
            $contextlist->add_system_context();
        }

        return $contextlist;
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param   approved_userlist       $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        $context = $userlist->get_context();

        if (!is_a($context, \context_system::class)) {
            return;
        }

        foreach ($userlist->get_userids() as $userid) {
            \local_faultreporting\faultreport::delete_reports_by_user($userid);
        }
    }

    /**
     * Implements delete_data_for_all_users_in_context
     * @param \context $context
     * @return void
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        if (!is_a($context, \context_system::class)) {
            return;
        }

        \local_faultreporting\faultreport::delete_all_reports();
    }

    /**
     * Implements delete_data_for_user
     * @param \core_privacy\local\request\approved_contextlist $contextlist
     * @return void
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        \local_faultreporting\faultreport::delete_reports_by_user($userid);
    }

    /**
     * Implements export_user_data
     * @param \core_privacy\local\request\approved_contextlist $contextlist
     * @return void
     */
    public static function export_user_data(approved_contextlist $contextlist) {
    }
}
