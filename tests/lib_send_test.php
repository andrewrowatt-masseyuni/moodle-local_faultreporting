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

/**
 * Tests for Fault Reporting
 *
 * @package    local_faultreporting
 * @category   test
 * @copyright  2025 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class lib_send_test extends \advanced_testcase {
    /**
     * Test sending a report to the external system
     * @return void
     *
     * @covers \local_faultreporting
     */
    public function test_send_report(): void {
        $this->setAdminUser();

        $this->resetAfterTest();

        $user1 = $this->getDataGenerator()->create_user(['username' => 'arowatt']);

        $username = getenv("ASSYST_API_USERNAME");
        $password = getenv("ASSYST_API_PASSWORD");

        set_config('assystapiurl', 'https://massey-dev.saas.axiossystems.com/assystREST/v2/events', 'local_faultreporting');
        set_config('assystapiusername', $username, 'local_faultreporting');
        set_config('assystapipassword', $password, 'local_faultreporting');
        set_config('assystaffecteduserfallback', 'ASSYSTSTUDENT', 'local_faultreporting');

        [$transactionstatus, $externalidorerrormsg] = faultreport::save_and_send_report($user1->id, 'title4', 'description4', 'payload4');
        $this->assertEquals(faultreport::TRANSACTION_SUCCESS, $transactionstatus, $externalidorerrormsg);
    }
}
