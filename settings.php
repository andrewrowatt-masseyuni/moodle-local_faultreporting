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
 * TODO describe file settings
 *
 * @package    local_faultreporting
 * @copyright  2025 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add(
        'localplugins',
        new admin_category('faultreportingfolder', get_string('pluginname', 'local_faultreporting'), false)
    );

    $ADMIN->add(
        'faultreportingfolder',
        new admin_externalpage(
            'local_faultreporting_faultreports',
            get_string('faultreports', 'local_faultreporting'),
            new moodle_url('/local/faultreporting/faultreports.php', []),
            'moodle/site:config'
        )
    );

    $settings = new admin_settingpage('local_faultreporting', get_string('pluginname', 'local_faultreporting'));

    if ($ADMIN->fulltree) {
        $settings->add(new admin_setting_configtext(
            'local_faultreporting/assystapiurl',
            get_string('assystapiurl', 'local_faultreporting'),
            get_string('assystapiurldesc', 'local_faultreporting'),
            'https://massey-dev.saas.axiossystems.com/assystREST/v2/events',
            PARAM_RAW,
            128,
        ));

        $settings->add(new admin_setting_configtext(
            'local_faultreporting/assystapiusername',
            get_string('assystapiusername', 'local_faultreporting'),
            get_string('assystapiusernamedesc', 'local_faultreporting'),
            '',
        ));

        $settings->add(new admin_setting_configtext(
            'local_faultreporting/assystapipassword',
            get_string('assystapipassword', 'local_faultreporting'),
            get_string('assystapipassworddesc', 'local_faultreporting'),
            '',
        ));

        $settings->add(new admin_setting_configtext(
            'local_faultreporting/assystaffecteduserfallback',
            get_string('assystapiaffecteduserfallback', 'local_faultreporting'),
            get_string('assystapiaffecteduserfallbackdesc', 'local_faultreporting'),
            'ASSYSTSTUDENT',
        ));

        $settings->add(new admin_setting_configtext(
            'local_faultreporting/assysteventsearchurl',
            get_string('assysteventsearchurl', 'local_faultreporting'),
            get_string('assysteventsearchurldesc', 'local_faultreporting'),
            'https://massey-dev.saas.axiossystems.com' .
                '/assystweb/application.do#eventsearch/EventSearchDelegatingDispatchAction.do?' .
                'dispatch=monitorInit&ajaxMonitor=false&eventSearchContext&queryProfileForm.columnProfileId=5' .
                '&event.lookup.eventRefRange=$externalid',
        ));

        $ADMIN->add('faultreportingfolder', $settings);

    }
}
