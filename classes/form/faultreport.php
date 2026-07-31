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

namespace local_faultreporting\form;

use context;
use context_system;
use html_writer;
use local_faultreporting\util;
use moodle_url;

/**
 * Class faultreport
 *
 * The form is used both as a standalone page (/local/faultreporting/faultreport.php) and, via
 * core_form/modalform, as a pop-up. The pop-up supplies its own Save and Cancel buttons, which is
 * the only difference between the two.
 *
 * The context of the page the report was made from is carried in hidden elements so that it
 * survives every round trip, including a re-render after a failed server-side validation.
 *
 * @package    local_faultreporting
 * @copyright  2025 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class faultreport extends \core_form\dynamic_form {
    /**
     * Hidden elements describing the page the report was made from, and their parameter types
     */
    private const CONTEXT_ELEMENTS = [
        'contextid' => PARAM_INT,
        'courseid' => PARAM_INT,
        'coursemoduleid' => PARAM_INT,
        'page' => PARAM_TEXT,
        'url' => PARAM_LOCALURL,
    ];

    /**
     * Define the form.
     */
    public function definition() {
        global $OUTPUT;

        $mform = $this->_form; // Don't forget the underscore!

        $mform->updateAttributes(
            ['class' => $mform->getAttribute('class') . ' local_faultreporting-faultreport']
        );

        // The form has a single section, so there is nothing for shortforms to collapse.
        $mform->setDisableShortforms();

        $mform->addElement('html', $OUTPUT->render_from_template(
            'local_faultreporting/faultreport_description',
            util::get_description_blocks()
        ));

        foreach (self::CONTEXT_ELEMENTS as $name => $type) {
            $mform->addElement('hidden', $name);
            $mform->setType($name, $type);
        }

        $this->add_user_details($mform);

        $mform->addElement(
            'textarea',
            'description',
            get_string('description', 'local_faultreporting')
            . html_writer::span(
                get_string('description_help', 'local_faultreporting'),
                'd-block font-italic'
            ),
            ['wrap' => 'virtual', 'rows' => 5, 'cols' => 50, 'style' => 'width: 100%;']
        );
        $mform->setType('description', PARAM_TEXT);
        $mform->addRule('description', null, 'required', null, 'client');

        $mform->addElement(
            'html',
            '<div class="d-flex justify-content-start local_faultreporting-faultreport-uigroup">'
        );

        // Optional fields, prefilled where the courseid and/or coursemoduleid parameters allow.
        $mform->addElement('text', 'coursename', get_string('coursename', 'local_faultreporting'), ['size' => 40]);
        $mform->setType('coursename', PARAM_TEXT);

        $mform->addElement('text', 'coursecode', get_string('coursecode', 'local_faultreporting'), ['size' => 6]);
        $mform->setType('coursecode', PARAM_TEXT);

        $mform->addElement('text', 'activityname', get_string('activityname', 'local_faultreporting'), ['size' => 32]);
        $mform->setType('activityname', PARAM_TEXT);

        $mform->addElement(
            'html',
            '</div>'
        );

        $this->add_buttons($mform);
    }

    /**
     * Adds the Submit and Cancel buttons, unless the form is displayed in a pop-up
     *
     * The pop-up adds its own Save and Cancel buttons to the modal footer. The flag is kept as a
     * hidden element so that it survives a re-render after a failed server-side validation, which
     * would otherwise show a second pair of buttons inside the modal body.
     *
     * @param \MoodleQuickForm $mform
     */
    private function add_buttons(\MoodleQuickForm $mform): void {
        if ($this->is_popup()) {
            $mform->addElement('hidden', 'nosubmit', 1);
            $mform->setType('nosubmit', PARAM_BOOL);

            // Labels sit above their fields, as the modal is too narrow for a label column.
            $this->set_display_vertical();

            return;
        }

        $buttonarray = [];
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('submitreport', 'local_faultreporting'));
        $buttonarray[] = $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
    }

    /**
     * Returns true if the form is being displayed in a pop-up rather than as a standalone page
     *
     * @return bool
     */
    private function is_popup(): bool {
        return (bool)$this->optional_param('nosubmit', 0, PARAM_BOOL);
    }

    /**
     * Adds the user's own details to the form
     *
     * The username, name and email are not editable, so they are shown as static text. Phone is
     * editable, so it is grouped with them to keep all four on a single row.
     *
     * @param \MoodleQuickForm $mform
     */
    private function add_user_details(\MoodleQuickForm $mform): void {
        global $USER;

        $usernamelabel = util::is_student()
            ? get_string('studentid', 'local_faultreporting')
            : get_string('username', 'local_faultreporting');

        $userdetails = [
            $usernamelabel => $USER->username,
            get_string('name') => $USER->firstname . ' ' . $USER->lastname,
            get_string('email') => $USER->email,
        ];

        $columns = '';

        foreach ($userdetails as $label => $value) {
            $columns .= html_writer::div(
                html_writer::div($label, 'small text-muted')
                    . html_writer::div(s($value), 'text-nowrap form-control form-control-disabled'),
                'mr-4'
            );
        }

        $phone = $mform->createElement(
            'text',
            'phone',
            get_string('phone'),
            ['size' => 20, 'placeholder' => get_string('phoneplaceholder', 'local_faultreporting')]
        );

        $userdetailsgroup = [
            $mform->createElement('static', 'userdetailsstatic', '', html_writer::div($columns, 'd-flex flex-wrap')),
            $phone,
        ];

        $mform->addGroup($userdetailsgroup, 'userdetails', '', '', false);

        // Grouping an element hides its label, but the phone label is wanted above its column.
        $phone->setHiddenLabel(false);

        $mform->setType('phone', PARAM_TEXT);
    }

    /**
     * Returns the context of the page the report was made from
     *
     * Falls back to the system context, which is also where a report made from a page with no more
     * specific context belongs.
     *
     * @return context
     */
    protected function get_context_for_dynamic_submission(): context {
        $contextid = $this->optional_param('contextid', 0, PARAM_INT);

        // The context may since have been deleted, for example where the link was cached.
        $context = $contextid ? context::instance_by_id($contextid, IGNORE_MISSING) : false;

        return $context ?: context_system::instance();
    }

    /**
     * Anyone with a real account may report a fault
     */
    protected function check_access_for_dynamic_submission(): void {
        if (!isloggedin() || isguestuser()) {
            throw new \moodle_exception('noguest');
        }
    }

    /**
     * Prefills the form from the page the report was made from
     */
    public function set_data_for_dynamic_submission(): void {
        $courseid = $this->optional_param('courseid', 0, PARAM_INT);
        $coursemoduleid = $this->optional_param('coursemoduleid', 0, PARAM_INT);
        $courseinfo = util::get_course_and_activity_info($courseid, $coursemoduleid);

        $this->set_data((object)[
            'contextid' => $this->get_context_for_dynamic_submission()->id,
            'courseid' => $courseid,
            'coursemoduleid' => $coursemoduleid,
            'page' => $this->optional_param('page', '', PARAM_TEXT),
            'url' => $this->get_from_url(),
            'description' => $this->optional_param('description', '', PARAM_TEXT),
            'phone' => util::get_phone(),
            'coursename' => $courseinfo['coursename'],
            'coursecode' => $courseinfo['coursecode'],
            'activityname' => $courseinfo['activityname'],
        ]);
    }

    /**
     * Returns the URL of the page the report was made from
     *
     * Falls back to the referer where no url parameter was supplied. Only URLs within this Moodle
     * site are accepted, as the value is used to redirect the user back to where they came from.
     *
     * @return string Empty string where the URL is unknown or is not local
     */
    private function get_from_url(): string {
        $url = $this->optional_param('url', '', PARAM_LOCALURL);

        if ($url === '') {
            $url = clean_param($_SERVER['HTTP_REFERER'] ?? '', PARAM_LOCALURL);
        }

        return $url;
    }

    /**
     * Saves and queues the report
     *
     * @return array Message to show the user once the pop-up has closed
     */
    public function process_dynamic_submission(): array {
        \local_faultreporting\faultreport::submit_form_data($this->get_data());

        return ['message' => get_string('reportqueued', 'local_faultreporting')];
    }

    /**
     * Returns the URL of the standalone version of this form
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        return new moodle_url('/local/faultreporting/faultreport.php');
    }
}
