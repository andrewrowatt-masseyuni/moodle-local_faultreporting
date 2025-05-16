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
        $this->setAdminUser();

        $user1 = $this->getDataGenerator()->create_user();
        $this->user1 = $user1;

        $user2 = $this->getDataGenerator()->create_user();
        $this->user2 = $user2;

        $user3 = $this->getDataGenerator()->create_user();
        $this->user3 = $user3;

        faultreport::save_report($user1->id, 'title1', 'description1', 'payload1');
        faultreport::save_report($user1->id, 'title2', 'description2', 'payload2');
        faultreport::save_report($user2->id, 'title3', 'description3', 'payload3');
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
}
