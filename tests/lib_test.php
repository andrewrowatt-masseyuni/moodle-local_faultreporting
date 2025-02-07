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

    /**
     * Covers basic adding and sending of a fault report
     *
     * @covers \local_faultreporting
     */
    public function test_add_faultreport(): void {
        global $DB;

        $this->resetAfterTest(true);

        $id = faultreport::save_report(2, 'title', 'description');

        $faultreport = $DB->get_record('local_faultreporting', ['id' => $id], '*', MUST_EXIST);

        $this->assertEquals(2, $faultreport->userid);
        $this->assertEquals(faultreport::STATUS_NEW, $faultreport->status);
    }
}
