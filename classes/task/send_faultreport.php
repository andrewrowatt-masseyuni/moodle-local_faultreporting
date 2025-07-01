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

namespace local_faultreporting\task;

/**
 * Class send_faultreport
 *
 * @package    local_faultreporting
 * @copyright  2025 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class send_faultreport extends \core\task\adhoc_task {
    /**
     * Get the name of the task.
     *
     * @return string The name of the task.
     */
    public function get_name(): string {
        return get_string('send_faultreport_task', 'local_faultreporting');
    }

    /**
     * Execute the task.
     */
    public function execute() {
        $reportid = $this->get_custom_data()->reportid;
        [$transactionstatus, $externalidorerrormsg] = \local_faultreporting\faultreport::send_report($reportid);

        if ($transactionstatus === \local_faultreporting\faultreport::TRANSACTION_FAILURE) {
            throw new \moodle_exception(
                "Fault report $reportid sending status: $transactionstatus, error message: $externalidorerrormsg."
            );
        }
    }
}
