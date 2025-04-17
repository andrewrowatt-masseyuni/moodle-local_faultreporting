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
 * TODO describe file faultreports
 *
 * @package    local_faultreporting
 * @copyright  2025 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_faultreporting\faultreport;

require('../../config.php');

require_login();

$url = new moodle_url('/local/faultreporting/faultreports.php', []);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_heading($SITE->fullname);
$PAGE->set_title(get_string('pluginname', 'local_faultreporting'));

require_capability('report/log:view', $PAGE->context); // Need to confirm this is the right capability.

$reports = [];

foreach(faultreport::get_reports() as $reportobject) {
    $reportarray = (array)$reportobject;
    if($reportarray['status'] == faultreport::STATUS_SEND_FAILURE) {
        $reportarray += [
            'canresend' => true,
        ];
    }

    $reports[] = $reportarray;
}


$data = [
    'sesskey' => sesskey(),
    'reports' => $reports,
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_faultreporting/faultreports', $data);
echo $OUTPUT->footer();
