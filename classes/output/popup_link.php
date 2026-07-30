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

namespace local_faultreporting\output;

use context;
use context_module;
use html_writer;
use moodle_page;
use moodle_url;

/**
 * A link that opens the fault report form in a pop-up
 *
 * This is the shared entry point for anything that wants to offer the pop-up, for example
 * filter_faultreporting. The link is a plain link to the standalone fault report page, so it still
 * works where JavaScript is unavailable.
 *
 * @package    local_faultreporting
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class popup_link {
    /**
     * Value of the data-action attribute that the pop-up JavaScript listens for
     */
    public const ACTION = 'local_faultreporting-faultreport';

    /**
     * Loads the JavaScript that turns the links into a pop-up
     *
     * Call this from somewhere that runs before the page footer is generated, such as a text
     * filter's setup() method. Requirements added after the footer are silently discarded.
     *
     * @param moodle_page $page
     */
    public static function require_js(moodle_page $page): void {
        if ($page->requires->should_create_one_time_item_now('local_faultreporting_popup')) {
            $page->requires->js_call_amd('local_faultreporting/popup', 'init');
        }
    }

    /**
     * Returns a link that opens the fault report form in a pop-up
     *
     * The course and activity the report relates to are derived from the given context, so that the
     * report is prefilled with them whether or not the pop-up is used.
     *
     * @param context $context Context of the page the link is shown on
     * @return string
     */
    public static function render(context $context): string {
        $params = self::get_context_params($context);

        return html_writer::link(
            new moodle_url('/local/faultreporting/faultreport.php', $params),
            get_string('faultreport', 'local_faultreporting'),
            ['data-action' => self::ACTION] + self::get_data_attributes($params)
        );
    }

    /**
     * Returns the course and activity that the given context belongs to
     *
     * @param context $context
     * @return array{contextid: int, courseid: int, coursemoduleid: int}
     */
    private static function get_context_params(context $context): array {
        $coursecontext = $context->get_course_context(false);

        return [
            'contextid' => $context->id,
            'courseid' => $coursecontext ? $coursecontext->instanceid : 0,
            'coursemoduleid' => $context instanceof context_module ? $context->instanceid : 0,
        ];
    }

    /**
     * Returns the given parameters as data attributes for the pop-up JavaScript to read
     *
     * The page title and URL are deliberately not included, as filtered text may be cached. The
     * JavaScript reads those from the browser when the link is used.
     *
     * @param array $params
     * @return array
     */
    private static function get_data_attributes(array $params): array {
        $attributes = [];

        foreach ($params as $name => $value) {
            $attributes["data-$name"] = $value;
        }

        return $attributes;
    }
}
