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
 * Privacy provider tests class
 *
 * @package    local_faultreporting
 * @category   test
 * @copyright  2025 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class provider_test extends \core_privacy\tests\provider_testcase {
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
     * Test for provider::get_metadata().
     *
     * @covers \local_faultreporting\privacy
     */
    public function test_get_metadata() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $collection = new \core_privacy\local\metadata\collection('local_faultreporting');
        $classname = privacy\provider::class;
        $classname::get_metadata($collection);

        // Check that the collection contains the expected items.
        $this->assertCount(2, $collection->get_collection());
    }

    /**
     * Test for provider::get_users_in_context().
     *
     * @covers \local_faultreporting\privacy
     */
    public function test_get_users_in_context() {
        $cmcontext = \context_system::instance();

        $userlist = new \core_privacy\local\request\userlist($cmcontext, 'local_faultreporting');
        privacy\provider::get_users_in_context($userlist);

        // Check that the userlist contains the expected users - order agnostic.
        $this->assertEquals(
            [],
            array_diff(
            [$this->user1->id, $this->user2->id],
            $userlist->get_userids()
        )
        );
    }

    /**
     * Test for provider::get_contexts_for_userid().
     *
     * @covers \local_faultreporting\privacy
     */
    public function test_get_contexts_for_userid() {
        $contextlist = privacy\provider::get_contexts_for_userid($this->user1->id);
        $this->assertCount(1, $contextlist);

        $contextlist = privacy\provider::get_contexts_for_userid($this->user3->id);
        $this->assertCount(0, $contextlist);
    }
}
