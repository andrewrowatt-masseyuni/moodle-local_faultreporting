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
 * Callback implementations for Fault Reporting
 *
 * @package    local_faultreporting
 * @copyright  2025 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Insert a link on the frontpage
 *
 * @param navigation_node $node Course node.
 */
function local_faultreporting_extend_navigation_frontpage(navigation_node $frontpage) {
    $frontpage->add(
        get_string('pluginname', 'local_faultreporting'),
        new moodle_url('/local/faultreporting/faultreport.php'),
        navigation_node::TYPE_CUSTOM,
    );
}

/**
 * Insert a link on any course page.
 *
 * @param navigation_node $node Course node.
 */
function local_faultreporting_extend_navigation_course(navigation_node $node) {
    if (isloggedin() && !isguestuser()) {
        $node->add(
            get_string('pluginname', 'local_faultreporting'),
            new moodle_url('/local/faultreporting/faultreport.php'),
            navigation_node::TYPE_CUSTOM,
        );
    }
}
