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
 * Standalone page for creating a new fault report
 *
 * The same form is also available as a pop-up, see local_faultreporting\output\popup_link.
 *
 * @package    local_faultreporting
 * @copyright  2025 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

require_login();

$url = new moodle_url('/local/faultreporting/faultreport.php');
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());

$PAGE->set_title(get_string('pluginname', 'local_faultreporting'));
$PAGE->set_heading(get_string('createnewfaultreport', 'local_faultreporting'));

// The form reads the page, url, description, courseid and coursemoduleid parameters itself.
$form = new \local_faultreporting\form\faultreport();
$form->set_data_for_dynamic_submission();

if ($form->is_cancelled()) {
    redirect(new moodle_url('/my/')); // To-do #3.
} else if ($formdata = $form->get_data()) {
    \local_faultreporting\faultreport::submit_form_data($formdata);

    // The url is cleaned as PARAM_LOCALURL by the form, so it is either local or empty.
    $redirecturl = $formdata->url === '' ? new moodle_url('/my/') : new moodle_url($formdata->url);

    redirect(
        $redirecturl,
        get_string('reportqueued', 'local_faultreporting'),
        null,
        \core\output\notification::NOTIFY_INFO
    );
}

echo $OUTPUT->header();

$form->display();

echo $OUTPUT->footer();
