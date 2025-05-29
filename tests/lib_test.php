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

defined('MOODLE_INTERNAL') || die();

global $CFG;

/**
 * Tests for Fault Reporting
 *
 * @package    local_faultreporting
 * @category   test
 * @copyright  2025 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class lib_test extends \advanced_testcase {
    /** @var \stdClass User object to share across tests. */
    protected \stdClass $user1;

    /** @var \stdClass User object to share across tests. */
    protected \stdClass $user2;

    /** @var \stdClass User object to share across tests. */
    protected \stdClass $user3;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void {
        $this->resetAfterTest();

        $user1 = $this->getDataGenerator()->create_user(['username' => 'arowatt']);
        $this->user1 = $user1;

        $user2 = $this->getDataGenerator()->create_user(['username' => '98186700']);
        $this->user2 = $user2;

        $user3 = $this->getDataGenerator()->create_user(['username' => 'st100585']);
        $this->user3 = $user3;

        faultreport::save_report($user1->id, "title1: $user1->username", 'description1', 'payload1');
        faultreport::save_report($user1->id, "title2: $user2->username", 'description2', 'payload2');
        faultreport::save_report($user2->id, "title3: $user3->username", 'description3', 'payload3');
        // Note there are no fault reports for user3.
    }

    /**
     * Covers basic adding and retrieving of fault reports
     *
     * @covers \local_faultreporting
     */
    public function test_add_faultreport(): void {
        $reports = faultreport::get_reports();
        $this->assertEquals(3, count($reports));

        $id = faultreport::save_report($this->user1->id, 'title', 'description', 'payload');

        $faultreport = faultreport::get_report_by_id($id);

        $this->assertEquals($this->user1->id, $faultreport->userid);
        $this->assertEquals(faultreport::STATUS_NEW, $faultreport->status);
    }

    /**
     * Test deleting fault reports for individual users and all users
     * @return void
     *
     * @covers \local_faultreporting
     */
    public function test_delete_faultreports(): void {
        $reports = faultreport::get_reports();
        $this->assertEquals(3, count($reports));

        faultreport::delete_reports_by_user($this->user2->id);
        $reports = faultreport::get_reports();
        $this->assertEquals(2, count($reports));

        faultreport::delete_reports_by_user($this->user3->id);
        $reports = faultreport::get_reports();
        $this->assertEquals(2, count($reports));

        faultreport::delete_all_reports();
        $reports = faultreport::get_reports();
        $this->assertEquals(0, count($reports));
    }

    /**
     * Test static utility functions
     * @return void
     *
     * @covers \local_faultreporting
     */
    public function test_util(): void {
        $this->setUser($this->user1);
        $this->assertFalse(util::is_student());
        $this->assertFalse(util::is_st_account());
        $this->assertTrue(util::is_staff());

        $this->setUser($this->user2);
        $this->assertTrue(util::is_student());
        $this->assertFalse(util::is_st_account());
        $this->assertFalse(util::is_staff());

        $this->setUser($this->user3);
        $this->assertTrue(util::is_st_account());
        $this->assertFalse(util::is_student());
        $this->assertFalse(util::is_staff());
    }

    /**
     * Test sending a report to the external system
     * @return void
     *
     * @covers \local_faultreporting
     */
    public function test_send_report(): void {
        // These should be set in github ref:
        // https://github.com/andrewrowatt-masseyuni/moodle-local_faultreporting/settings/secrets/actions.
        $username = getenv("ASSYST_API_USERNAME");
        $password = getenv("ASSYST_API_PASSWORD");
        ($environment = getenv("PHPUNIT_ENVIRONMENT")) || ($environment = 'localhost');

        set_config('assystapiurl', 'https://massey-dev.saas.axiossystems.com/assystREST/v2/events', 'local_faultreporting');
        set_config('assystapiusername', $username, 'local_faultreporting');
        set_config('assystapipassword', $password, 'local_faultreporting');
        set_config('assystaffecteduserfallback', 'ASSYSTSTUDENT', 'local_faultreporting');

        [$transactionstatus, $externalidorerrormsg] = faultreport::save_and_send_report(
            $this->user1->id, "unittest: env:$environment", 'unittest', 'unittest');
        $this->assertEquals(faultreport::TRANSACTION_SUCCESS, $transactionstatus, $externalidorerrormsg);

        // To verify: https://massey-dev.saas.axiossystems.com/assystnet/application.jsp#eventMonitor/10.
    }
}
