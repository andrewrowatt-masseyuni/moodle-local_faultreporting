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
        global $USER;

        $mform = $this->_form; // Don't forget the underscore!

        $mform->addElement('hidden', 'diagnosticinfo', $this->_customdata['diagnosticinfo']);
        $mform->setType('diagnosticinfo', PARAM_TEXT);

        $mform->addElement('header', 'general', get_string('basicinformationgroup', 'local_faultreporting'));

        $mform->addElement('text', 'username',
            util::is_student() ? get_string('studentid', 'local_faultreporting') : get_string('username', 'local_faultreporting'),
            ['size' => 8, 'disabled' => 'disabled']);
        $mform->setDefault('username', $USER->username);
        $mform->setType('username', PARAM_TEXT);

        $mform->addElement('text', 'name', get_string('name'), ['size' => 32]);
        $mform->setDefault('name', $USER->firstname . ' ' . $USER->lastname);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('text', 'email', get_string('email'), ['size' => 32]);
        $mform->setDefault('email', $USER->email);
        $mform->setType('email', PARAM_TEXT);
        $mform->addRule('email', null, 'required', null, 'client');

        $mform->addElement('text', 'phone', get_string('phone'), ['size' => 32]);
        $mform->setDefault('phone', util::get_phone());
        $mform->setType('phone', PARAM_TEXT);
        $mform->addRule('phone', null, 'required', null, 'client');

        $mform->addElement(
            'textarea',
            'description',
            get_string('description', 'local_faultreporting'),
            'wrap="virtual" rows="5" cols="50"');
        $mform->setDefault('description', '');
        $mform->setType('description', PARAM_TEXT);
        $mform->addHelpButton('description', 'description', 'local_faultreporting');

        // Arguably a bit of a hack to get the help text to display in my preferred place.
        $mform->addElement('html', html_writer::div(
            '<i class="icon fa fa-info-circle " aria-hidden="true"></i>' . get_string('description_help', 'local_faultreporting'),
            'alert alert-info help_text', ['style' => 'margin-left: calc(25% + 7px );']));

        $mform->addElement('header', 'diagnosticinformation', get_string('diagnosticinformation', 'local_faultreporting'));
        $mform->setExpanded('diagnosticinformation', false); // ...collapse by default

        $mform->addElement('html', html_writer::div(
            $this->_customdata['diagnosticinfo'],
            null, null));

        $mform->closeHeaderBefore('buttonar');
        // End of owner group.

        $buttonarray = [];
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('submitreport', 'local_faultreporting'));
        $buttonarray[] = $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
    }
}
