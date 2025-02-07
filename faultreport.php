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

namespace local_faultreporting;

use moodle_url;
use context_system;

/**
 * TODO describe file faultreport
 *
 * @package    local_faultreporting
 * @copyright  2025 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

require_login();

$frompage = optional_param('page', '-', PARAM_TEXT);
$fromurl = optional_param('url', '-', PARAM_TEXT);

if ($fromurl == '-' && array_key_exists('HTTP_REFERER', $_SERVER)) {
    $fromurl = $_SERVER['HTTP_REFERER'];
}

$url = new moodle_url('/local/faultreporting/faultreport.php', []);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());

$PAGE->set_heading($SITE->fullname);

$clientinfo = util::get_client_info();

$diagnosticinfo =
    "Page: $frompage\n" .
    "URL: $fromurl\n\n" .
    "Browser: $clientinfo[browser]\n" .
    "OS: $clientinfo[operatingsystem]\n" .
    "Useragent: $_SERVER[HTTP_USER_AGENT]\n\n";

$form = new \local_faultreporting\form\faultreport(null,
 ['diagnosticinfo' => $diagnosticinfo]);

if ($form->is_cancelled()) {
    // If there is a cancel element on the form, and it was pressed,
    // then the `is_cancelled()` function will return true.
    // You can handle the cancel operation here.
} else if ($formdata = $form->get_data()) {
    $description =
        "Name: $formdata->name\n" .
        "Email: $formdata->email\n" .
        "Phone: $formdata->phone\n\n" .
        "Description:\n$formdata->description\n\n" .
        "Diagnostic Info:\n$formdata->diagnosticinfo";

    [$transactionstatus, $externalidorerrormsg] = faultreport::save_and_send_report(
        $USER->id, 'Log a Stream Request', $description);

    switch ($transactionstatus) {
        case faultreport::TRANSACTION_SUCCESS:
            $message = get_string('reportsuccessful', 'local_faultreporting', ['externalid' => $externalidorerrormsg]);
            $messagetype = \core\output\notification::NOTIFY_SUCCESS;
            break;
        case faultreport::TRANSACTION_FAILURE:
            $message = get_string('reporterror', 'local_faultreporting');
            $messagetype = \core\output\notification::NOTIFY_ERROR;
            break;
    }

    redirect(new moodle_url('/my'), $message, null, $messagetype);
}

echo $OUTPUT->header();

$form->display();

echo $OUTPUT->footer();
