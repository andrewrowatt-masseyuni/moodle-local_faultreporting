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

namespace local_faultreporting\reportbuilder\local\systemreports;

use context_system;
use core_reportbuilder\local\entities\user;
use core_reportbuilder\local\report\action;
use core_reportbuilder\system_report;
use lang_string;
use local_faultreporting\faultreport as faultreport_model;
use local_faultreporting\reportbuilder\local\entities\faultreport;
use moodle_url;
use pix_icon;
use stdClass;

/**
 * Fault reports system report class implementation
 *
 * @package    local_faultreporting
 * @copyright  2025 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class faultreports extends system_report {
    /**
     * Initialise report, we need to set the main table, load our entities and set columns/filters
     */
    protected function initialise(): void {
        $entitymain = new faultreport();
        $entitymainalias = $entitymain->get_table_alias('local_faultreporting');

        $this->set_main_table('local_faultreporting', $entitymainalias);
        $this->add_entity($entitymain);

        // Add base fields required by action callbacks.
        $this->add_base_fields("{$entitymainalias}.id, {$entitymainalias}.status");

        // Join the user entity.
        $entityuser = new user();
        $entityuseralias = $entityuser->get_table_alias('user');
        $this->add_entity($entityuser->add_join(
            "LEFT JOIN {user} {$entityuseralias} ON {$entityuseralias}.id = {$entitymainalias}.userid"
        ));

        $this->add_columns();
        $this->add_filters();
        $this->add_actions();

        $this->set_initial_sort_column('faultreport:timecreated', SORT_DESC);
    }

    /**
     * Validates access to view this report
     *
     * @return bool
     */
    protected function can_view(): bool {
        return has_capability('report/log:view', context_system::instance());
    }

    /**
     * Get the visible name of the report
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('faultreports', 'local_faultreporting');
    }

    /**
     * Adds the columns we want to display in the report
     */
    protected function add_columns(): void {
        $this->add_columns_from_entities([
            'faultreport:status',
            'user:fullname',
            'faultreport:summary',
            'faultreport:timecreated',
        ]);

        $this->get_column('user:fullname')
            ->set_title(new lang_string('user'));
    }

    /**
     * Adds the filters we want to display in the report
     */
    protected function add_filters(): void {
        $this->add_filters_from_entities([
            'faultreport:status',
            'faultreport:description',
            'user:fullname',
            'faultreport:timecreated',
        ]);
    }

    /**
     * Add the system report actions
     */
    protected function add_actions(): void {
        // Resend action — only shown for new or failed reports.
        $this->add_action((new action(
            new moodle_url('/local/faultreporting/faultreports.php', [
                'action' => 'resend',
                'id' => ':id',
                'sesskey' => sesskey(),
            ]),
            new pix_icon('t/reload', ''),
            [],
            false,
            new lang_string('resend', 'local_faultreporting'),
        ))->add_callback(static function (stdClass $row): bool {
            return $row->status == faultreport_model::STATUS_NEW
                || $row->status == faultreport_model::STATUS_SEND_FAILURE;
        }));

        // Delete action.
        $this->add_action(new action(
            new moodle_url('/local/faultreporting/faultreports.php', [
                'action' => 'delete',
                'id' => ':id',
                'sesskey' => sesskey(),
            ]),
            new pix_icon('t/delete', ''),
            [],
            false,
            new lang_string('delete'),
        ));
    }
}
