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
use local_faultreporting\util;
use html_writer;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

/**
 * Class faultreport
 *
 * @package    local_faultreporting
 * @copyright  2025 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class faultreport extends \moodleform {
    /**
     * Define the form.
     */
    public function definition() {
        $mform = $this->_form; // Don't forget the underscore!

        $mform->addElement('hidden', 'diagnosticinfo', $this->_customdata['diagnosticinfo']);
        $mform->setType('diagnosticinfo', PARAM_TEXT);

        $mform->addElement('hidden', 'fromurl', $this->_customdata['fromurl']);
        $mform->setType('fromurl', PARAM_TEXT);

        $mform->addElement('hidden', 'url', $this->_customdata['url']);
        $mform->setType('url', PARAM_TEXT);

        $mform->addElement('header', 'general', get_string('basicinformationgroup', 'local_faultreporting'));

        $this->add_user_details($mform);

        $mform->addElement(
            'textarea',
            'description',
            get_string('description', 'local_faultreporting')
            . html_writer::span(
                get_string('description_help', 'local_faultreporting'),
                'd-block font-italic'
            ),
            'wrap="virtual" rows="5" cols="50"'
        );
        $mform->setDefault('description', $this->_customdata['description'] ?? '');
        $mform->setType('description', PARAM_TEXT);
        $mform->addRule('description', null, 'required', null, 'client');

        $mform->addElement(
            'html',
            '<div class="d-flex justify-content-start local_faultreporting-faultreport-uigroup">'
        );

        // Optional fields, prefilled where the courseid and/or coursemoduleid parameters allow.
        $mform->addElement('text', 'coursename', get_string('coursename', 'local_faultreporting'), ['size' => 40]);
        $mform->setDefault('coursename', $this->_customdata['coursename'] ?? '');
        $mform->setType('coursename', PARAM_TEXT);

        $mform->addElement('text', 'coursecode', get_string('coursecode', 'local_faultreporting'), ['size' => 6]);
        $mform->setDefault('coursecode', $this->_customdata['coursecode'] ?? '');
        $mform->setType('coursecode', PARAM_TEXT);

        $mform->addElement('text', 'activityname', get_string('activityname', 'local_faultreporting'), ['size' => 32]);
        $mform->setDefault('activityname', $this->_customdata['activityname'] ?? '');
        $mform->setType('activityname', PARAM_TEXT);

        $mform->addElement(
            'html',
            '</div>'
        );

        /*
        // Note this duplicates the URL in the diagnostic information, but is editable by the user.
        $mform->addElement('text', 'url', get_string('url'), ['size' => 100]);
        $mform->setDefault('url', $this->_customdata['url'] ?? '');
        $mform->setType('url', PARAM_TEXT);
        */

        /*

        $mform->addElement('header', 'diagnosticinformation', get_string('diagnosticinformation', 'local_faultreporting'));
        $mform->setExpanded('diagnosticinformation', false); // Collapse by default.

        $mform->addElement('html', html_writer::tag(
            'div',
            s($this->_customdata['diagnosticinfo']),
            ['style' => 'white-space: pre-wrap;']
        ));

        $mform->closeHeaderBefore('buttonar');
        // End of owner group.
        */

        $buttonarray = [];
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('submitreport', 'local_faultreporting'));
        $buttonarray[] = $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
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
                html_writer::div($label, 'small text-muted') . html_writer::div(s($value), 'text-nowrap form-control form-control-disabled'),
                'mr-4'  
            );
        }

        $phone = $mform->createElement('text', 'phone', get_string('phone'), ['size' => 20, 'placeholder' => get_string('phoneplaceholder', 'local_faultreporting')]);

        $userdetailsgroup = [
            $mform->createElement('static', 'userdetailsstatic', '', html_writer::div($columns, 'd-flex flex-wrap')),
            $phone,
        ];

        $mform->addGroup($userdetailsgroup, 'userdetails', '', '', false);

        // Grouping an element hides its label, but the phone label is wanted above its column.
        $phone->setHiddenLabel(false);

        $mform->setDefault('phone', util::get_phone());
        $mform->setType('phone', PARAM_TEXT);
    }
}
