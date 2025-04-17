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

$action = optional_param('action', null, PARAM_TEXT);
$reportid = optional_param('id', null, PARAM_INT);

if ($action && $reportid) {
    require_sesskey();

    switch ($action) {
        case 'resend':
            // Do something with the report.

            break;
    }

    // Redirect as Moodle good practice to remove the session key from the URL.
    redirect($url,'[Feedback]');
}

$reports = [];

foreach(faultreport::get_reports() as $reportobject) {
    $reportarray = (array)$reportobject;
    $reportarray += [
        'statusdescription' => faultreport::get_status_description($reportarray['status']),
    ];

    switch($reportarray['status']){
        case faultreport::STATUS_NEW:
            $reportarray += [
                'statusclass' => 'warning',
            ];
            break;
        case faultreport::STATUS_SENT:
            $reportarray += [
                'statusclass' => 'success',
            ];
            break;
        case faultreport::STATUS_SEND_FAILURE:
            $reportarray += [
                'statusclass' => 'danger',
            ];
            break;
    }
    
    if($reportarray['status'] == faultreport::STATUS_SEND_FAILURE || $reportarray['status'] == faultreport::STATUS_NEW) {
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
