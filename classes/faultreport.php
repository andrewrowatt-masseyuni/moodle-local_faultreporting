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
 * Class faultreport
 *
 * @package    local_faultreporting
 * @copyright  2025 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class faultreport {

    /**
     * Report is new and unsent
     */
    const STATUS_NEW = 0;

    /**
     * Report has been successfully sent
     */
    const STATUS_SENT = 1;

    /**
     * An issue prevented the report from being sent
     */
    const STATUS_SEND_FAILURE = 2;

    /**
     * Sending transaction was successful
     */
    const TRANSACTION_SUCCESS = 'OK';

    /**
     * Sending transaction was unsuccessful
     */
    const TRANSACTION_FAILURE = 'ERROR';

    /**
     * Sends a report
     *
     * Returns false if an error occuring during then send process
     * Does not update the database
     *
     * @param mixed $data
     * @return string externalid
     */
    public static function send_report(string $reportedby, string $title, string $description): array {
        global $DB;

        return [self::TRANSACTION_FAILURE, "Error: Message not sent."];
        // return [self::TRANSACTION_SUCCESS, "externalID:TBA"];
    }

    /**
     * Saves a report to the database for later sending
     *
     * Returns the id of the saved report
     *
     * @param mixed $data
     * @return int
     */
    public static function save_report(int $userid, string $title, string $description): int {
        global $DB;

        $time = time();

        $data = [
            'userid' => $userid,
            'title' => $title,
            'description' => $description,
            'externalid' => '',
            'status' => self::STATUS_NEW,
            'errormsg' => '',
            'timecreated' => $time,
            'timemodified' => $time,
        ];

        return $DB->insert_record('local_faultreporting', $data, true);
    }

    /**
     * Saves a report to the database and sends it
     *
     * Returns the status of the transaction
     *
     * @param mixed $data
     * @return string
     */
    public static function save_and_send_report(int $userid, string $title, string $description): array {
        global $DB;

        $id = self::save_report($userid, $title, $description);

        $report = $DB->get_record('local_faultreporting', ['id' => $id], '*', MUST_EXIST);

        $user = \core_user::get_user($userid);

        [$transactionstatus, $externalidorerrormsg] = self::send_report($user->username, $title, $description);

        switch ($transactionstatus) {
            case self::TRANSACTION_SUCCESS:
                $report->externalid = $externalidorerrormsg;
                $report->status = self::STATUS_SENT;
            case self::TRANSACTION_FAILURE:
                $report->status = self::STATUS_SEND_FAILURE;
                $report->errormsg = $externalidorerrormsg;
        }

        $report->timemodified = time();
        $DB->update_record('local_faultreporting', $report);

        return [$transactionstatus, $externalidorerrormsg];
    }
}
