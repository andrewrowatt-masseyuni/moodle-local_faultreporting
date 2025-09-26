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
$PAGE->set_heading(get_string('faultreports', 'local_faultreporting'));
$PAGE->set_title(get_string('faultreports', 'local_faultreporting'));

require_capability('report/log:view', $PAGE->context); // Need to confirm this is the right capability.

$action = optional_param('action', null, PARAM_TEXT);
$reportid = optional_param('id', null, PARAM_INT);

if ($action && $reportid) {
    require_sesskey();

    switch ($action) {
        case 'resend':
            faultreport::queue_send_report($reportid);

            $message = get_string('reportqueued', 'local_faultreporting');
                $messagetype = \core\output\notification::NOTIFY_INFO;


            redirect($url, $message, null, $messagetype);
            break;
        case 'delete':
            faultreport::delete_report($reportid);

            $message = get_string('reportdeleted', 'local_faultreporting');
                $messagetype = \core\output\notification::NOTIFY_INFO;


            redirect($url, $message, null, $messagetype);
    }
}

$reports = [];

foreach (faultreport::get_reports() as $reportobject) {
    $reportarray = (array)$reportobject;
    [$shortcode, $description, $cssclasshint] = faultreport::get_status_description($reportarray['status']);

    $reportarray += [
        'statusshortcode' => $shortcode,
        'statusdescription' => $description,
        'statusclass' => $cssclasshint,
    ];

    switch($reportarray['status']){
        case faultreport::STATUS_NEW:
            $reportarray += [
                'showstatusdescription' => true,
            ];
            break;
        case faultreport::STATUS_SENT:
            $reportarray += [
                'hasexternalid' => true,
                'assysteventsearchurl' => faultreport::get_assyst_event_search_url($reportarray['externalid']),
            ];
            break;
        case faultreport::STATUS_SEND_FAILURE:
            $reportarray += [
                'haserrormessage' => $reportarray['errormsg'],
            ];
            break;
    }

    if ($reportarray['status'] == faultreport::STATUS_SEND_FAILURE || $reportarray['status'] == faultreport::STATUS_NEW) {
        $reportarray += [
            'canresend' => true,
        ];
    }

    $reports[] = $reportarray;
}


$data = [
    'sesskey' => sesskey(),
    'reportcount' => count($reports),
    'reports' => $reports,
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_faultreporting/faultreports', $data);
echo $OUTPUT->footer();
