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
use core_reportbuilder\local\filters\boolean_select;
use lang_string;
use moodle_exception;
use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\report\{column, filter};
use report_fundae\reportbuilder\local\helpers\fundae_format;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/completion/criteria/completion_criteria.php');

/**
 * @package report_fundae
 * @author 3iPunt <https://www.tresipunt.com/>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 3iPunt <https://www.tresipunt.com/>
 */
class fundae_course_completion extends base {

    /** @var string $lastenroldatefield */
    protected string $lastenroldatefield;

    /** @var string $lastenroldatejoin */
    protected string $lastenroldatejoin;


    /**
     * Database tables that this entity uses and their default aliases
     *
     * @return array
     */
    protected function get_default_table_aliases(): array {
        return [
            'course_completion' => 'cc',
            'course' => 'c',
            'grade_grades' => 'gg',
            'grade_items' => "gi",
            "course_completion_criteria" => "ccc"
        ];
    }

    /**
     * The default machine-readable name for this entity that will be used in the internal names of the columns/filters
     *
     * @return string
     */
    protected function get_default_entity_name(): string {
        return 'course_completion';
    }

    /**
     * The default title for this entity in the list of columns/conditions/filters in the report builder
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('entitycoursecompletion', 'report_fundae');
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
            $this
                ->add_filter($filter)
                ->add_condition($filter);
        }

        return $this;
    }

    /**
     * Sets the SQL expression for the last enroldate (available outside of this join)
     *
     * @param string $lastenroldatefield
     * @param string|null $lastenroldatejoin If the lastenroldatefield needs an extra join, pass it here
     * @return self
     */
    public function set_last_enroldate_field_sql(string $lastenroldatefield, ?string $lastenroldatejoin = null): self {
        $this->lastenroldatefield = $lastenroldatefield;
        $this->lastenroldatejoin = (string) $lastenroldatejoin;

        return $this;
    }

    /**
     * Returns list of all available columns
     *
     * @return column[]
     * @throws coding_exception
     */
    protected function get_all_columns() : array {
        $tablealias = $this->get_table_alias('course_completion');
        $tablealiascourse = $this->get_table_alias('course');
        $tablealiasgrade = $this->get_table_alias('grade_grades');
        $tablealiasgradeitem = $this->get_table_alias('grade_items');
        $tablealiascompletioncriteria = $this->get_table_alias('course_completion_criteria');

        // Completed status.
        $columns[] = (new column(
            'completed',
            new lang_string('completed', 'completion'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_field("CASE WHEN {$tablealias}.timecompleted > 0 THEN 1 ELSE 0 END", 'completed')
            ->set_is_sortable(true)
            ->add_callback([fundae_format::class, 'checkbox_as_text']);

        // Progress.
        $column = (new column(
            'progress',
            new lang_string('course_completion_progress', 'report_fundae'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_field("{$tablealiascourse}.id", 'courseid')
            ->add_field("{$tablealiascourse}.enablecompletion")
            ->add_field("{$tablealias}.userid")
            ->add_callback([fundae_format::class, 'completion_progress']);

        $columns[] = $column;

        // Progress (percentage).
        $column = (new column(
            'progresspercent',
            new lang_string('course_completion_progress_percent', 'report_fundae'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_field("{$tablealiascourse}.id", 'courseid')
            ->add_field("{$tablealiascourse}.enablecompletion")
            ->add_field("{$tablealias}.userid")
            ->add_callback([fundae_format::class, 'completion_progress'], true);

        $columns[] = $column;

        // Time enrolled/started/completed/reaggregated.
        $allcolumns = ['timeenrolled', 'timestarted', 'timecompleted', 'reaggregate'];
        foreach ($allcolumns as $column) {
            $columns[] = (new column(
                $column,
                new lang_string("course_completion_${column}", 'report_fundae'),
                $this->get_entity_name()
            ))
                ->add_joins($this->get_joins())
                ->add_field("{$tablealias}.{$column}")
                ->set_is_sortable(true)
                ->add_callback([fundae_format::class, 'userdate']);
        }

        $currenttime = time();

        // Days taking course (days since course start date until completion or until current date if not completed).
        $columns[] = (new column(
            'dayscourse',
            new lang_string('course_completion_days_course', 'report_fundae'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_field("(
                CASE
                    WHEN {$tablealias}.timecompleted > 0 THEN
                        {$tablealias}.timecompleted
                    ELSE
                        {$currenttime}
                END - {$tablealiascourse}.startdate) / " . DAYSECS, 'dayscourse')
            ->set_is_sortable(true)
            ->add_callback([fundae_format::class, 'days']);

        // Days since last enrolment (days since last enrolment date until completion or until current date if not completed).
        if ($this->lastenroldatefield) {
            $columns[] = (new column(
                'daysenrolled',
                new lang_string('course_completion_days_enrolled', 'report_fundae'),
                $this->get_entity_name()
            ))
                ->add_joins($this->get_joins())
                ->add_join($this->lastenroldatejoin)
                ->add_field("(
                CASE
                    WHEN {$tablealias}.timecompleted > 0 THEN
                        {$tablealias}.timecompleted
                    ELSE
                        {$currenttime}
                END - {$this->lastenroldatefield}) / " . DAYSECS, 'daysenrolled')
                ->set_is_sortable(true)
                ->add_callback([fundae_format::class, 'days']);

        }

        // Student course grade.
        $columns[] = (new column(
            'grade',
            new lang_string('gradenoun'),
            $this->get_entity_name()
        ))
            ->add_join("LEFT JOIN {grade_items} $tablealiasgradeitem ON ($tablealiasgradeitem.itemtype = 'course'
                 AND $tablealiascourse.id = $tablealiasgradeitem.courseid )")
            ->add_join("LEFT JOIN {grade_grades} $tablealiasgrade ON ( u.id = $tablealiasgrade.userid
                 AND $tablealiasgradeitem.id = $tablealiasgrade.itemid )")
            ->add_joins($this->get_joins())
            ->add_fields("$tablealiasgrade.finalgrade")
            ->set_is_sortable(true)
            ->add_callback(function ($value) {
                if (!$value) {
                    return '';
                }
                return format_float($value, 2);
            });

        // Required grade.
        $criteriatype = COMPLETION_CRITERIA_TYPE_GRADE;
        $columns[] = (new column(
            'requiredgrade',
            new lang_string('graderequired', 'completion'),
            $this->get_entity_name()
        ))
            ->add_join("LEFT JOIN {course_completion_criteria} $tablealiascompletioncriteria
                         ON ($tablealiascourse.id = $tablealiascompletioncriteria.course
                         AND $tablealiascompletioncriteria.criteriatype = $criteriatype
                         AND $tablealiascompletioncriteria.module IS NULL)")
            ->add_joins($this->get_joins())
            ->add_field("$tablealiascompletioncriteria.gradepass")
            ->set_is_sortable(true)
            ->add_callback(function ($value) {
                if (!$value) {
                    return '';
                }
                return format_float($value, 2);
            });

        return $columns;
    }

    /**
     *
     * @return filter[]
     * @throws moodle_exception
     */
    protected function get_all_filters() : array {
        $tablealias = $this->get_table_alias('course_completion');
        // Completed status.
        $filters[] = (new filter(
            boolean_select::class,
            'completed_filter',
            new lang_string('completed', 'completion'),
            $this->get_entity_name(),
            "CASE WHEN {$tablealias}.timecompleted > 0 THEN 1 ELSE 0 END"
        ))->add_joins($this->get_joins());

        return $filters;
    }
}
