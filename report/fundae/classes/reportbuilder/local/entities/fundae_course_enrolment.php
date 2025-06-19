<?php
// This file is part of Moodle Workplace https://moodle.com/workplace based on Moodle
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
//
// Moodle Workplace™ Code is the collection of software scripts
// (plugins and modifications, and any derivations thereof) that are
// exclusively owned and licensed by Moodle under the terms of this
// proprietary Moodle Workplace License ("MWL") alongside Moodle's open
// software package offering which itself is freely downloadable at
// "download.moodle.org" and which is provided by Moodle under a single
// GNU General Public License version 3.0, dated 29 June 2007 ("GPL").
// MWL is strictly controlled by Moodle Pty Ltd and its certified
// premium partners. Wherever conflicting terms exist, the terms of the
// MWL are binding and shall prevail.

/**
 * @package report_fundae
 * @author 3iPunt <https://www.tresipunt.com/>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 3iPunt <https://www.tresipunt.com/>
 */

namespace report_fundae\reportbuilder\local\entities;

use coding_exception;
use core_reportbuilder\local\filters\select;
use core_user\output\status_field;
use enrol_plugin;
use lang_string;
use moodle_exception;
use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\report\{column, filter};
use core_course\reportbuilder\local\formatters\enrolment;
use core_reportbuilder\local\filters\date;
use report_fundae\reportbuilder\local\helpers\fundae_format;


defined('MOODLE_INTERNAL') || die();

/**
 * @package report_fundae
 * @author 3iPunt <https://www.tresipunt.com/>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 3iPunt <https://www.tresipunt.com/>
 */
class fundae_course_enrolment extends base {

    /**
     * Database tables that this entity uses and their default aliases
     *
     * @return array
     */
    protected function get_default_table_aliases(): array {
        return ['user_enrolments' => 'ue', 'enrol' => 'e'];
    }

    /**
     * The default machine-readable name for this entity that will be used in the internal names of the columns/filters
     *
     * @return string
     */
    protected function get_default_entity_name(): string {
        return 'course_enrolment';
    }

    /**
     * The default title for this entity in the list of columns/conditions/filters in the report builder
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('entitycourseenrolment', 'report_fundae');
    }


    /**
     * @return base
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function initialise(): base {
        $columns = $this->get_all_columns();
        foreach ($columns as $column) {
            $this->add_column($column);
        }

        $filters = $this->get_all_filters();
        foreach ($filters as $filter) {
            $this->add_filter($filter);
        }

        $conditions = $this->get_all_filters();
        foreach ($conditions as $condition) {
            $this->add_condition($condition);
        }
        return $this;
    }

    /**
     * Generate SQL snippet suitable for returning enrolment status field
     *
     * @return string
     * @throws coding_exception
     */
    private function get_status_field_sql() : string {
        $time = round(time(), -2); // Rounding helps caching in DB.
        $tablealias = $this->get_table_alias('user_enrolments');
        $tablealiasenrol = $this->get_table_alias('enrol');

        return  "
            CASE WHEN {$tablealias}.status = " . ENROL_USER_ACTIVE . "
                 THEN CASE WHEN ({$tablealias}.timestart > {$time})
                             OR ({$tablealias}.timeend > 0 AND {$tablealias}.timeend < {$time})
                             OR ({$tablealiasenrol}.status = " . ENROL_INSTANCE_DISABLED . ")
                           THEN " . status_field::STATUS_NOT_CURRENT . "
                           ELSE " . status_field::STATUS_ACTIVE . "
                      END
                 ELSE " . status_field::STATUS_SUSPENDED . "
            END";
    }

    /**
     * Returns list of all available columns
     *
     * @return column[]
     * @throws coding_exception
     */
    protected function get_all_columns() : array {
        $tablealias = $this->get_table_alias('user_enrolments');
        $tablealiasenrol = $this->get_table_alias('enrol');

        // Enrolment method.
        $columns[] = (new column(
            'method',
            new lang_string('enrolmentmethod', 'enrol'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_field("{$tablealiasenrol}.enrol")
            ->add_field("{$tablealias}.enrolid")
            ->set_is_sortable(true)
            ->add_callback([enrolment::class, 'enrolment_name']);

        // Enrolment time created.
        $columns[] = (new column(
            'timecreated',
            new lang_string('enroltimecreated', 'enrol'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_field("{$tablealias}.timecreated")
            ->set_is_sortable(true)
            ->add_callback([fundae_format::class, 'userdate']);

        // Enrolment time started.
        $columns[] = (new column(
            'timestarted',
            new lang_string('course_enrolment_timestarted', 'report_fundae'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_field("CASE WHEN {$tablealias}.timestart = 0
                              THEN {$tablealias}.timecreated
                              ELSE {$tablealias}.timestart
                         END", 'timestarted')
            ->set_is_sortable(true)
            ->add_callback([fundae_format::class, 'userdate']);

        // Enrolment time ended.
        $columns[] = (new column(
            'timeended',
            new lang_string('course_enrolment_timeended', 'report_fundae'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_field("{$tablealias}.timeend")
            ->set_is_sortable(true)
            ->add_callback([fundae_format::class, 'userdate']);

        // Enrolment status.
        $columns[] = (new column(
            'status',
            new lang_string('course_enrolment_status', 'report_fundae'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_field($this->get_status_field_sql(), 'status')
            ->set_is_sortable(true)
            ->add_callback([enrolment::class, 'enrolment_status']);

        return $columns;
    }

    /**
     * Return available filters/conditions
     *
     * @return filter[]
     * @throws moodle_exception
     */
    protected function get_all_filters(): array {
        $tablealias = $this->get_table_alias('user_enrolments');
        $tablealiasenrol = $this->get_table_alias('enrol');

        // Enrolment method.
        $enrolmentmethods = function() {
            return array_map(static function(enrol_plugin $plugin) {
                return get_string('pluginname', 'enrol_' . $plugin->get_name());
            }, enrol_get_plugins(true));
        };

        $filters[] = (new filter(
            select::class,
            'method_filter',
            new lang_string('enrolmentmethod', 'enrol'),
            $this->get_entity_name(),
            "{$tablealiasenrol}.enrol"
        ))
            ->add_joins($this->get_joins())
            ->set_options_callback($enrolmentmethods);

        // Enrolment time created.
        $filters[] = (new filter(
            date::class,
            'timecreated_filter',
            new lang_string('enroltimecreated', 'enrol'),
            $this->get_entity_name(),
            "{$tablealias}.timecreated"
        ))
            ->add_joins($this->get_joins());

        // Enrolment time started.
        $filters[] = (new filter(
            date::class,
            'timestarted_filter',
            new lang_string('course_enrolment_timestarted', 'report_fundae'),
            $this->get_entity_name(),
            "CASE WHEN {$tablealias}.timestart = 0
                  THEN {$tablealias}.timecreated
                  ELSE {$tablealias}.timestart
             END"
        ))
            ->add_joins($this->get_joins());

        // Enrolment time ended.
        $filters[] = (new filter(
            date::class,
            'timeended_filter',
            new lang_string('course_enrolment_timeended', 'report_fundae'),
            $this->get_entity_name(),
            "{$tablealias}.timeend"
        ))
            ->add_joins($this->get_joins());

        // Enrolment status.
        $enrolmentstatuses = [
            status_field::STATUS_ACTIVE => new lang_string('participationactive', 'enrol'),
            status_field::STATUS_SUSPENDED => new lang_string('participationsuspended', 'enrol'),
            status_field::STATUS_NOT_CURRENT => new lang_string('participationnotcurrent', 'enrol'),
        ];

        $filters[] = (new filter(
            select::class,
            'status_filter',
            new lang_string('course_enrolment_status', 'report_fundae'),
            $this->get_entity_name(),
            $this->get_status_field_sql()
        ))
            ->add_joins($this->get_joins())
            ->set_options($enrolmentstatuses);

        return $filters;
    }
}
