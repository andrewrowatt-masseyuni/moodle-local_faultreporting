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

use local_faultreporting\form\faultreport as faultreport_form;

/**
 * Tests for the fault report form, which is used both standalone and in a pop-up
 *
 * @package    local_faultreporting
 * @category   test
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_faultreporting\form\faultreport
 */
final class form_test extends \advanced_testcase {
    /**
     * Builds a submitted instance of the form, as the pop-up submits it
     *
     * @param array $data Submitted form data
     * @return faultreport_form
     */
    private function get_submitted_form(array $data): faultreport_form {
        $ajaxformdata = faultreport_form::mock_ajax_submit($data);

        return new faultreport_form(null, null, 'post', '', [], true, $ajaxformdata);
    }

    /**
     * A report is accepted where a description was given
     */
    public function test_get_data(): void {
        $this->resetAfterTest();
        $this->setUser($this->getDataGenerator()->create_user());

        $form = $this->get_submitted_form([
            'description' => 'The page will not load.',
            'phone' => '021 555 0100',
            'coursename' => 'Test course 1',
            'coursecode' => 'TC1',
            'activityname' => 'Test page 1',
        ]);

        $data = $form->get_data();

        $this->assertNotNull($data);
        $this->assertSame('The page will not load.', $data->description);
    }

    /**
     * A description is required, and the rule is enforced server side as well as client side
     */
    public function test_description_is_required(): void {
        $this->resetAfterTest();
        $this->setUser($this->getDataGenerator()->create_user());

        $form = $this->get_submitted_form(['description' => '']);

        $this->assertNull($form->get_data());
    }

    /**
     * An external url is discarded, as it is used to redirect the user back to where they came from
     */
    public function test_external_url_is_discarded(): void {
        $this->resetAfterTest();
        $this->setUser($this->getDataGenerator()->create_user());

        $form = $this->get_submitted_form([
            'description' => 'The page will not load.',
            'url' => 'https://evil.example.com/phish',
        ]);

        $this->assertSame('', $form->get_data()->url);
    }

    /**
     * A local url is kept
     */
    public function test_local_url_is_kept(): void {
        global $CFG;

        $this->resetAfterTest();
        $this->setUser($this->getDataGenerator()->create_user());

        $form = $this->get_submitted_form([
            'description' => 'The page will not load.',
            'url' => "$CFG->wwwroot/course/view.php?id=2",
        ]);

        $this->assertSame("$CFG->wwwroot/course/view.php?id=2", $form->get_data()->url);
    }

    /**
     * The pop-up supplies its own buttons, and keeps doing so after a failed validation
     */
    public function test_popup_has_no_buttons_of_its_own(): void {
        $this->resetAfterTest();
        $this->setUser($this->getDataGenerator()->create_user());

        $form = new faultreport_form(null, null, 'post', '', [], true, ['nosubmit' => 1]);
        $html = $form->render();

        $this->assertStringNotContainsString('id_submitbutton', $html);

        // A failed validation re-renders the form, which must not gain a second set of buttons.
        $resubmitted = $this->get_submitted_form(['description' => '', 'nosubmit' => 1]);

        $this->assertNull($resubmitted->get_data());
        $this->assertStringNotContainsString('id_submitbutton', $resubmitted->render());
    }

    /**
     * The standalone page keeps its own Submit and Cancel buttons
     */
    public function test_standalone_form_has_buttons(): void {
        $this->resetAfterTest();
        $this->setUser($this->getDataGenerator()->create_user());

        $form = new faultreport_form();

        $this->assertStringContainsString('id_submitbutton', $form->render());
    }

    /**
     * Course and activity details are prefilled from the courseid and coursemoduleid parameters
     */
    public function test_set_data_prefills_course_details(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course(['fullname' => 'Test course 1', 'shortname' => 'TC1']);
        $page = $this->getDataGenerator()->create_module('page', ['course' => $course->id, 'name' => 'Test page 1']);
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);
        $this->setUser($user);

        $form = new faultreport_form(null, null, 'post', '', [], true, [
            'courseid' => $course->id,
            'coursemoduleid' => $page->cmid,
        ]);
        $form->set_data_for_dynamic_submission();

        $html = $form->render();

        $this->assertStringContainsString('Test course 1', $html);
        $this->assertStringContainsString('TC1', $html);
        $this->assertStringContainsString('Test page 1', $html);
    }
}
