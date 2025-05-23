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

/**
 * English language pack for Fault Reporting
 *
 * @package    local_faultreporting
 * @category   string
 * @copyright  2025 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['assystapiaffecteduserfallback'] = 'Assyst API Affected user fallback';
$string['assystapiaffecteduserfallbackdesc'] = 'Shortcode of affected user that is used when the API reports a error with the Moodle supplied shortcode (username)';
$string['assystapipassword'] = 'Assyst API password';
$string['assystapipassworddesc'] = 'Assyst API password';
$string['assystapiurl'] = 'Assyst API Events endpoint';
$string['assystapiurldesc'] = 'Assyst API Events endpoint';
$string['assystapiusername'] = 'Assyst API username';
$string['assystapiusernamedesc'] = 'Assyst API username';
$string['assysteventsearchurl'] = 'Assyst event search URL';
$string['assysteventsearchurldesc'] = 'URL for searching Assyst by event id. Use $externalid as a placeholder for the event id.';
$string['basicinformationgroup'] = 'General';
$string['createnewfaultreport'] = 'Create new fault report';
$string['defaultsummary'] = 'Log a Stream Request';
$string['description'] = 'Description';
$string['description_help'] = 'Include as much information as possible.';
$string['descriptiongeneralmd'] = 'Something not working quite right with Stream? Use this form to log a support request. Remember to include as much information as possible.';
$string['descriptionstaccountmd'] = 'Please ensure that your *Email address* is correct.';
$string['descriptionstaffmd'] = 'While you can use this form, as a staff member you are encouraged to:

-   [log a **fault** directly in AskUs](https://massey.saas.axiossystems.com/assystnet/application.jsp#serviceOfferings/510), or

-   [log a **support request** directly in AskUs](https://massey.saas.axiossystems.com/assystnet/application.jsp#serviceOfferings/386).';
$string['diagnosticinformation'] = 'Diagnostic information for support personal';
$string['externalid'] = 'Assyst ID';
$string['faultreport'] = 'Fault report';
$string['faultreports'] = 'Fault reports';
$string['noreports'] = 'No fault reports';
$string['pluginname'] = 'Fault Reporting';
$string['privacy:metadata:local_faultreporting'] = 'Information about individual fault reports.';
$string['privacy:metadata:local_faultreporting:userid'] = 'The user who logged the fault report.';
$string['privacy:metadata:local_faultreporting:description'] = 'The description of the report, which may contain personal information.';
$string['reporterror'] = 'Well, this is embrassing. There was a fault sending your fault report. The technical teams have been advised.';
$string['reportresendsuccessful'] = 'Report resend was successful. Reference number is {$a->externalid}';
$string['reportsuccessful'] = 'Report successful. Reference number is {$a->externalid}.';
$string['resend'] = 'Resend report';
$string['statusnew'] = 'If this status persists then resend as it may indicate a problem with the Assyst API.';
$string['statussendfailure'] = 'Error sending report to Assyst';
$string['statussent'] = 'Report successfully sent to Assyst';
$string['studentid'] = 'Student ID';
$string['submitreport'] = 'Submit report';
$string['username'] = 'Username';
