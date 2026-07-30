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

/**
 * Tests for the shared helpers used by both the fault report page and the pop-up
 *
 * @package    local_faultreporting
 * @category   test
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_faultreporting\util
 */
final class util_test extends \advanced_testcase {
    /**
     * The diagnostic information reports the page the fault was logged from
     */
    public function test_build_diagnostic_info(): void {
        $this->resetAfterTest();

        $_SERVER['HTTP_USER_AGENT'] =
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36';

        $diagnosticinfo = util::build_diagnostic_info('Test page', 'https://example.com/course/view.php?id=2', 2, 7);

        $this->assertStringContainsString('Page: Test page', $diagnosticinfo);
        $this->assertStringContainsString('URL or HTTP Referer: https://example.com/course/view.php?id=2', $diagnosticinfo);
        $this->assertStringContainsString('Course ID: 2', $diagnosticinfo);
        $this->assertStringContainsString('Course module ID: 7', $diagnosticinfo);
        $this->assertStringContainsString('Browser: Chrome', $diagnosticinfo);
        $this->assertStringContainsString('OS: Windows', $diagnosticinfo);
    }

    /**
     * The useragent is optional, as it is absent under CLI and in tests
     */
    public function test_build_diagnostic_info_without_useragent(): void {
        $this->resetAfterTest();

        unset($_SERVER['HTTP_USER_AGENT']);

        $diagnosticinfo = util::build_diagnostic_info('Test page', '');

        $this->assertStringContainsString("Useragent: \n", $diagnosticinfo);
        $this->assertStringContainsString('Course ID: 0', $diagnosticinfo);
    }

    /**
     * Students only see the general description block
     */
    public function test_get_description_blocks_for_student(): void {
        $this->resetAfterTest();
        $this->setUser($this->getDataGenerator()->create_user(['username' => '98186700']));

        $blocks = util::get_description_blocks();

        $this->assertNotEmpty($blocks['descriptiongeneral']);
        $this->assertSame('', $blocks['descriptionstaccount']);
        $this->assertSame('', $blocks['descriptionstaff']);
    }

    /**
     * "st" accounts are additionally asked to check their email address
     */
    public function test_get_description_blocks_for_st_account(): void {
        $this->resetAfterTest();
        $this->setUser($this->getDataGenerator()->create_user(['username' => 'st999999']));

        $blocks = util::get_description_blocks();

        $this->assertNotEmpty($blocks['descriptiongeneral']);
        $this->assertNotEmpty($blocks['descriptionstaccount']);
        $this->assertSame('', $blocks['descriptionstaff']);
    }

    /**
     * Staff are additionally pointed at AskUs
     */
    public function test_get_description_blocks_for_staff(): void {
        $this->resetAfterTest();
        $this->setUser($this->getDataGenerator()->create_user(['username' => 'arowatt']));

        $blocks = util::get_description_blocks();

        $this->assertNotEmpty($blocks['descriptiongeneral']);
        $this->assertSame('', $blocks['descriptionstaccount']);
        $this->assertNotEmpty($blocks['descriptionstaff']);
    }
}
