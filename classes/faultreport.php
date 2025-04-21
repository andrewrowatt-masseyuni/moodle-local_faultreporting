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
     * Retry the transaction with the default username
     */
    const TRANSACTION_RETRY_WITH_DEFAULT = 'RETRY';

    /**
     * Builds the prescibed/template JSON payload for Assyst
     *
     * @param string $reportedby
     * @param string $affecteduser
     * @param string $summary
     * @param string $description
     * @return bool|string
     */
    public static function build_assyst_json_payload(
            string $reportedby, string $affecteduser,
            string $summary, string $description): string {
        $reportedbyshortcode = strtoupper($reportedby);
        $affectedusershortcode = strtoupper($affecteduser);

        $data = [
            'entityDefinitionId' => 319,
            'entityDefinitionType' => 2,
            'eventTypeEnum' => 'INCIDENT',
            'shortDescription' => $summary,
            'remarks' => $description,
            'affectedUser' => [
                'resolvingParameters' => [[
                    'parameterName' => 'shortCode',
                    'parameterValue' => $affectedusershortcode,
                ]],
            ],
            'reportingUser' => [
                'resolvingParameters' => [[
                    'parameterName' => 'shortCode',
                    'parameterValue' => $reportedbyshortcode,
                ]],
            ],
            'itemA' => [
                'resolvingParameters' => [[
                    'parameterName' => 'shortCode',
                    'parameterValue' => 'OTHER',
                ]],
            ],
            'itemB' => [
                'resolvingParameters' => [[
                    'parameterName' => 'shortCode',
                    'parameterValue' => 'OTHER',
                ]],
            ],
            'category' => [
                'resolvingParameters' => [[
                    'parameterName' => 'shortCode',
                    'parameterValue' => 'DEFECT',
                ]],
            ],
            'assignedServDept' => [
                'resolvingParameters' => [[
                    'parameterName' => 'shortCode',
                    'parameterValue' => 'STREAM SERVICE DESK',
                ]],
            ],
            'impact' => [
                'resolvingParameters' => [[
                    'parameterName' => 'shortCode',
                    'parameterValue' => 'INDIVIDUAL',
                ]],
            ],
            'priority' => [
                'resolvingParameters' => [[
                    'parameterName' => 'shortCode',
                    'parameterValue' => '3-WORK NOT AFFECTED',
                ]],
            ],
        ];

        return json_encode($data);
    }

    /**
     * Sends a report to Assyst using the Assyst API and curl
     *
     * Returns false if an error occuring during then send process
     * Does not update the database
     *
     * @param  string $reportedby
     * @param  string $affecteduser
     * @param  string $summary
     * @param  string $description
     * @param  bool $useaffecteduserfallback
     * @return array transaction status, externalid or error message
     */
    public static function send_report_to_assyst(
            string $reportedby, string $affecteduser,
            string $summary, string $description,
            bool $useaffecteduserfallback = false): array {
        global $DB;

        $endpoint = get_config('local_faultreporting', 'assystapiurl');
        $username = get_config('local_faultreporting', 'assystapiusername');
        $password = get_config('local_faultreporting', 'assystapipassword');
        $affecteduserfallback = get_config('local_faultreporting', 'assystaffecteduserfallback');

        // ... used in retry scenarios
        if ($useaffecteduserfallback) {
            $affecteduser = $affecteduserfallback;
        }

        $auth = base64_encode("$username:$password");

        $payload = self::build_assyst_json_payload($reportedby, $affecteduser, $summary, $description);

        $ch = curl_init($endpoint);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "Authorization: Basic $auth",
            'Accept: application/json',
        ]);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        if (util::is_localhost()) {
            // ... if localhost, disable SSL verification
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        }

        $responseraw = curl_exec($ch);
        $response = json_decode($responseraw);

        $httpcode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        $curlerrorcode = curl_error($ch);
        curl_close($ch);

        if ($responseraw) {
            // ... we have a JSON response from Assyst
            switch ($httpcode) {
                case 201: /* created */
                    $eventref = $response->eventRef;
                    return [self::TRANSACTION_SUCCESS, $eventref];
                case 400: /* Bad request */
                    // ... in some cases we can recover from a 400 error
                    switch($response->type) {
                        case 'ComplexValidationException':
                            if ($useaffecteduserfallback) {
                                return [self::TRANSACTION_FAILURE,
                                    "HTTP Error 400: Bad request. Assyst API response:
                                    type: $response->type, message: $response->message. useaffecteduserfallback is true."];
                            } else {
                                return [self::TRANSACTION_RETRY_WITH_DEFAULT,
                                    "HTTP Error 400: Bad request. Assyst API response:
                                    type: $response->type, message: $response->message"];
                            }
                        default:
                            return [self::TRANSACTION_FAILURE,
                                "HTTP Error 400: Bad request. Assyst API response:
                                type: $response->type, message: $response->message"];
                    }
                default:
                    return [self::TRANSACTION_FAILURE,
                        "HTTP Error $httpcode. Report not sent. curl error: $curlerrorcode."];
            }
        } else {
            // ... no JSON response from Assyst, but we can perform some basic checks
            switch ($httpcode) {
                case 401: /* Unauthorized */
                    return [self::TRANSACTION_FAILURE,
                        "HTTP Error 401: Unauthorized. Check Assyst API Username and Password."];
                default:
                    return [self::TRANSACTION_FAILURE,
                        "HTTP Error $httpcode. Report not sent. curl error: $curlerrorcode."];
            }
        }
    }

    /**
     * Saves a report to the database for later sending
     *
     * Returns the id of the saved report
     *
     * @param int $userid // Moodle user id.
     * @param string $summary
     * @param string $description
     * @param string $payload // Full description of the fault that is passed to Assyst.
     * @return int
     */
    public static function save_report(int $userid, string $summary, string $description, string $payload): int {
        global $DB;

        $time = time();

        $data = [
            'userid' => $userid,
            'summary' => $summary,
            'description' => $description,
            'payload' => $payload,
            'externalid' => '',
            'status' => self::STATUS_NEW,
            'errormsg' => '',
            'timecreated' => $time,
            'timemodified' => $time,
        ];

        return $DB->insert_record('local_faultreporting', $data, true);
    }

    /**
     * Sends a report. Can also be used to resend a report
     *
     * Returns the status of the transaction
     *
     * @param  int $id
     * @return array transaction status, externalid or error message
     */
    public static function send_report(int $id): array {
        global $DB;

        $report = $DB->get_record('local_faultreporting', ['id' => $id], '*', MUST_EXIST);

        $user = \core_user::get_user($report->userid);

        [$transactionstatus, $externalidorerrormsg] = self::send_report_to_assyst(
            $user->username, $user->username,
            $report->summary, $report->payload);

        switch ($transactionstatus) {
            case self::TRANSACTION_SUCCESS:
                $report->externalid = $externalidorerrormsg;
                $report->status = self::STATUS_SENT;
                break;
            case self::TRANSACTION_RETRY_WITH_DEFAULT:
                // ... perform a one-time retry forcing the default username
                [$transactionstatus, $externalidorerrormsg] =
                    self::send_report_to_assyst($user->username, $user->username, $report->summary, $report->payload, true);

                if ($transactionstatus == self::TRANSACTION_SUCCESS) {
                    $report->status = self::STATUS_SENT;
                    $report->externalid = $externalidorerrormsg;
                } else {
                    $report->status = self::STATUS_SEND_FAILURE;
                    $report->errormsg = $externalidorerrormsg;

                    // ... specify failure in case of retry failure due to the fallback username being incorrect
                    $transactionstatus = self::TRANSACTION_FAILURE;
                }
                break;

            default:
                $report->status = self::STATUS_SEND_FAILURE;
                $report->errormsg = $externalidorerrormsg;
        }

        $report->timemodified = time();
        $DB->update_record('local_faultreporting', $report);

        return [$transactionstatus, $externalidorerrormsg];
    }

    /**
     * Saves a report to the database and sends it
     *
     * Returns the status of the transaction
     *
     * @param int $userid // Moodle user id.
     * @param string $summary
     * @param string $description
     * @param string $payload // Full description of the fault that is passed to Assyst.
     * @return array transaction status, externalid or error message
     */
    public static function save_and_send_report(int $userid, string $summary, string $description, string $payload): array {
        $id = self::save_report($userid, $summary, $description, $payload);
        return self::send_report($id);
    }

    /**
     * Returns all reports from the database
     *
     * @return array
     */
    public static function get_reports(): array {
        global $DB;

        $sql = 'SELECT fr.*,
            trim(concat(u.firstname, \' \', u.lastname)) as user,
            u.username as username
            FROM {local_faultreporting} fr
            JOIN {user} u ON u.id = fr.userid
            order by case when fr.status = 1 then -1 else fr.status end desc, fr.timecreated desc';

        return $DB->get_records_sql($sql);
    }

    /**
     * Returns the status description for a given status code
     *
     * @param int $status
     * @return array shortcode, long description, bootstrap css class hint
     */
    public static function get_status_description($status): array {
        switch ($status) {
            case self::STATUS_NEW:
                return ['New',
                    get_string('statusnew', 'local_faultreporting'),
                    'warning'];
            case self::STATUS_SENT:
                return ['Ok',
                    get_string('statussent', 'local_faultreporting'),
                    'success'];
            case self::STATUS_SEND_FAILURE:
                return ['Error',
                    get_string('statussendfailure', 'local_faultreporting'),
                    'danger'];
            default:
                throw new \moodle_exception("Unknown status code: $status");
        }
    }

    /**
     * Returns the Assyst event search URL with the external ID replaced
     *
     * Used on the system administrator fault reports page
     *
     * @param string $externalid
     * @return string
     */
    public static function get_assyst_event_search_url($externalid): string {
        $url = get_config('local_faultreporting', 'assysteventsearchurl');
        return str_replace('$externalid', $externalid, $url);
    }
}
