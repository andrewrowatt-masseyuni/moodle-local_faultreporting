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

namespace local_faultreporting\reportbuilder\local\entities;

use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\filters\date;
use core_reportbuilder\local\filters\select;
use core_reportbuilder\local\filters\text;
use core_reportbuilder\local\helpers\format;
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\report\filter;
use html_writer;
use lang_string;
use local_faultreporting\faultreport as faultreport_model;
use moodle_url;
use stdClass;

/**
 * Fault report entity class implementation
 *
 * @package    local_faultreporting
 * @copyright  2025 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class faultreport extends base {
    /**
     * Database tables that this entity uses
     *
     * @return string[]
     */
    protected function get_default_tables(): array {
        return [
            'local_faultreporting',
        ];
    }

    /**
     * The default title for this entity in the list of columns/conditions/filters in the report builder
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('faultreport', 'local_faultreporting');
    }

    /**
     * Initialise the entity
     *
     * @return base
     */
    public function initialise(): base {
        foreach ($this->get_all_columns() as $column) {
            $this->add_column($column);
        }

        foreach ($this->get_all_filters() as $filter) {
            $this
                ->add_filter($filter)
                ->add_condition($filter);
        }

        return $this;
    }

    /**
     * Returns list of all available columns
     *
     * @return column[]
     */
    protected function get_all_columns(): array {
        $tablealias = $this->get_table_alias('local_faultreporting');

        // Status column.
        $columns[] = (new column(
            'status',
            new lang_string('status'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_fields("{$tablealias}.status, {$tablealias}.externalid, {$tablealias}.errormsg")
            ->set_is_sortable(true, ["{$tablealias}.status"])
            ->add_callback(static function (?int $status, stdClass $row): string {
                if ($status === null) {
                    return '';
                }
                [$shortcode, , $cssclass] = faultreport_model::get_status_description($status);

                $badge = html_writer::tag('span', $shortcode, ['class' => "badge badge-{$cssclass}"]);

                if ($status === faultreport_model::STATUS_SEND_FAILURE && !empty($row->errormsg)) {
                    $badge .= ' ' . html_writer::tag('small', $row->errormsg, ['class' => 'text-muted']);
                } else if ($status === faultreport_model::STATUS_SENT && !empty($row->externalid)) {
                    $assysturl = faultreport_model::get_assyst_event_search_url($row->externalid);
                    $link = html_writer::link($assysturl, $row->externalid, ['target' => '_blank']);
                    $badge .= ' ' . html_writer::tag('small', get_string('externalid', 'local_faultreporting') . ': ' . $link);
                }

                return $badge;
            });

        // Summary column.
        $columns[] = (new column(
            'summary',
            new lang_string('description'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$tablealias}.description")
            ->set_is_sortable(true);

        // Time created column.
        $columns[] = (new column(
            'timecreated',
            new lang_string('date'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_field("{$tablealias}.timecreated")
            ->set_is_sortable(true)
            ->add_callback([format::class, 'userdate'], get_string('strftimedatetimeshortaccurate', 'core_langconfig'));

        return $columns;
    }

    /**
     * Returns list of all available filters
     *
     * @return filter[]
     */
    protected function get_all_filters(): array {
        $tablealias = $this->get_table_alias('local_faultreporting');

        // Status filter.
        $filters[] = (new filter(
            select::class,
            'status',
            new lang_string('status'),
            $this->get_entity_name(),
            "{$tablealias}.status"
        ))
            ->add_joins($this->get_joins())
            ->set_options([
                faultreport_model::STATUS_NEW => get_string('statuscodenew', 'local_faultreporting'),
                faultreport_model::STATUS_SENT => get_string('statuscodesent', 'local_faultreporting'),
                faultreport_model::STATUS_SEND_FAILURE => get_string('statuscodesendfailure', 'local_faultreporting'),
            ]);

        // Description filter.
        $filters[] = (new filter(
            text::class,
            'description',
            new lang_string('description'),
            $this->get_entity_name(),
            "{$tablealias}.description"
        ))
            ->add_joins($this->get_joins());

        // Time created filter.
        $filters[] = (new filter(
            date::class,
            'timecreated',
            new lang_string('date'),
            $this->get_entity_name(),
            "{$tablealias}.timecreated"
        ))
            ->add_joins($this->get_joins())
            ->set_limited_operators([
                date::DATE_ANY,
                date::DATE_RANGE,
                date::DATE_PREVIOUS,
                date::DATE_CURRENT,
            ]);

        return $filters;
    }
}
