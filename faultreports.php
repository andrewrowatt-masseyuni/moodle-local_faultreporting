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
 * Fault reports administration page
 *
 * @package    local_faultreporting
 * @copyright  2025 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_reportbuilder\system_report_factory;
use local_faultreporting\faultreport;
use local_faultreporting\reportbuilder\local\systemreports\faultreports as faultreports_report;

require('../../config.php');

require_login();

$url = new moodle_url('/local/faultreporting/faultreports.php');
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('faultreports', 'local_faultreporting'));
$PAGE->set_title(get_string('faultreports', 'local_faultreporting'));

require_capability('report/log:view', $PAGE->context);

$action = optional_param('action', null, PARAM_TEXT);
$reportid = optional_param('id', null, PARAM_INT);

if ($action && $reportid) {
    require_sesskey();

    switch ($action) {
        case 'resend':
            faultreport::queue_send_report($reportid);
            redirect($url, get_string('reportqueued', 'local_faultreporting'), null, \core\output\notification::NOTIFY_INFO);
            break;
        case 'delete':
            faultreport::delete_report($reportid);
            redirect($url, get_string('reportdeleted', 'local_faultreporting'), null, \core\output\notification::NOTIFY_INFO);
            break;
    }
}

echo $OUTPUT->header();
$report = system_report_factory::create(faultreports_report::class, context_system::instance());
echo $report->output();
echo $OUTPUT->footer();
