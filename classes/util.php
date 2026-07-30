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
require_once("$CFG->dirroot/user/profile/lib.php");

require_once("$CFG->dirroot/local/faultreporting/thirdparty/spyc/Spyc.php");
require_once("$CFG->dirroot/local/faultreporting/thirdparty/device-detector/autoload.php");

use DeviceDetector\ClientHints;
use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\AbstractDeviceParser;

use DeviceDetector\Parser\Client\Browser;
use DeviceDetector\Parser\OperatingSystem;

/**
 * Class util
 *
 * @package    local_faultreporting
 * @copyright  2025 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class util {
    /**
     * Returns true if username is 8 digits
     *
     * @return bool
     */
    public static function is_student(): bool {
        global $USER;

        return preg_match('/^\d{8}$/', $USER->username) == 1;
    }

    /**
     * Returns true if username is "st" account format
     *
     * @return bool
     */
    public static function is_st_account(): bool {
        global $USER;

        return preg_match('/^st\d{6}$/', $USER->username) == 1;
    }

    /**
     * Returns true if username is not a student or "st" account
     *
     * @return bool
     */
    public static function is_staff(): bool {
        return !self::is_student() && !self::is_st_account();
    }

    /**
     * Gets phone number from user profile field with fallback to built-in phone fields
     *
     * @return string
     */
    public static function get_phone(): string {
        global $USER;

        $phone = '';

        $cpf = profile_user_record($USER->id);

        // First check MU SMS CellPhone User Profile field...
        if (property_exists($cpf, 'CellPhone')) {
            $phone = $cpf->CellPhone;
        }

        // If empty, fallback to built-in Moodle phone fields...
        if (!$phone) {
            $phone = $USER->phone2;
        }

        if (!$phone) {
            $phone = $USER->phone1;
        }

        return $phone;
    }

    /**
     * Returns course and activity information derived from optional courseid and coursemoduleid parameters
     *
     * The course name is the course fullname and the course code is the course shortname. If no
     * courseid is supplied then the course is derived from the course that the supplied
     * coursemoduleid belongs to.
     *
     * Only courses and activities that the current user is allowed to see are reported on.
     *
     * @param int $courseid Course id, 0 if not supplied
     * @param int $coursemoduleid Course module id, 0 if not supplied
     * @return array{coursename: string, coursecode: string, activityname: string}
     */
    public static function get_course_and_activity_info(int $courseid = 0, int $coursemoduleid = 0): array {
        $cm = null;

        if ($coursemoduleid > 0) {
            $cm = get_coursemodule_from_id('', $coursemoduleid, 0, false, IGNORE_MISSING) ?: null;
        }

        if ($cm && !$courseid) {
            // ... fall back to the course that the supplied activity belongs to
            $courseid = $cm->course;
        }

        $coursenames = self::get_course_names($courseid);

        return [
            'coursename' => $coursenames['coursename'],
            'coursecode' => $coursenames['coursecode'],
            'activityname' => self::get_activity_name($cm),
        ];
    }

    /**
     * Returns the fullname and shortname of a course
     *
     * Empty strings are returned if the course does not exist or the current user cannot access it.
     *
     * @param int $courseid Course id, 0 if not supplied
     * @return array{coursename: string, coursecode: string}
     */
    private static function get_course_names(int $courseid): array {
        global $DB;

        $course = $courseid ? $DB->get_record('course', ['id' => $courseid]) : false;

        if (!$course || !can_access_course($course)) {
            return ['coursename' => '', 'coursecode' => ''];
        }

        return ['coursename' => $course->fullname, 'coursecode' => substr($course->shortname, 0, 6)];
    }

    /**
     * Returns the name of the given activity
     *
     * An empty string is returned if no activity is supplied or the current user cannot see the
     * activity.
     *
     * @param \stdClass|null $cm Course module record as returned by get_coursemodule_from_id()
     * @return string
     */
    private static function get_activity_name(?\stdClass $cm): string {
        if (!$cm) {
            return '';
        }

        $cminfo = get_fast_modinfo($cm->course)->get_cm($cm->id);

        if (!can_access_course(get_course($cm->course)) || !$cminfo->uservisible) {
            return '';
        }

        return $cminfo->name;
    }

    /**
     * Returns basic browser/useragent information
     * @return array{browser: string, operatingsystem: string}
     */
    public static function get_client_info(): array {
        $useragent = $_SERVER['HTTP_USER_AGENT'];
        $clienthints = ClientHints::factory($_SERVER); // ... client hints are optional

        $dd = new DeviceDetector($useragent, $clienthints);

        $dd->parse();

        return [
            'browser' => $dd->getClient('name'),
            'operatingsystem' => $dd->getOs('name'),
        ];
    }

    /**
     * Returns true current user is on localhost
     *
     * @return bool
     */
    public static function is_localhost(): bool {
        $localhost = ['127.0.0.1', '::1'];

        if (!array_key_exists('REMOTE_ADDR', $_SERVER)) {
            // May not mean localhost, but we can't check.
            return false;
        }

        return in_array($_SERVER['REMOTE_ADDR'], $localhost);
    }
}
