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

        return in_array($_SERVER['REMOTE_ADDR'], $localhost);
    }
}
