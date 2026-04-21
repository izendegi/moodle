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

namespace report_fundae\reportbuilder\local\entities;

use coding_exception;
use core_reportbuilder\local\filters\date;
use lang_string;
use moodle_exception;
use report_fundae\reportbuilder\local\helpers\fundae_format;
use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\report\{column, filter};

/**
 * @package report_fundae
 * @author 3iPunt <https://www.tresipunt.com/>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 3iPunt <https://www.tresipunt.com/>
 */
class fundae_user_messaging extends base {

    /**
     * Database tables that this entity uses and their default aliases
     *
     * @return array
     */
    protected function get_default_table_aliases(): array {
        return [
            'message' => 'm',
        ];
    }

    /**
     * The default machine-readable name for this entity that will be used in the internal names of the columns/filters
     *
     * @return string
     */
    protected function get_default_entity_name(): string {
        return 'user_messaging';
    }

    /**
     * The default title for this entity in the list of columns/conditions/filters in the report builder
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('messages', 'report_fundae');
    }

    /**
     * @return base
     * @throws coding_exception
     */
    public function initialise(): base {
        $columns = $this->get_all_columns();
        foreach ($columns as $column) {
            $this->add_column($column);
        }

        // All the filters defined by the entity can also be used as conditions.
        $filters = $this->get_all_filters();
        foreach ($filters as $filter) {
            $this
                ->add_filter($filter)
                ->add_condition($filter);
        }

        return $this;
    }

    /**
     * Returns list of available columns
     *
     * @return column[]
     * @throws coding_exception
     */
    protected function get_all_columns(): array {
        $columns[] = (new column(
            'messagestostudents',
            new lang_string('messagestostudents', 'report_fundae'),
            $this->get_entity_name()
        ))
            ->add_field('u.id', 'userid')
            ->add_field('c.id', 'courseid')
            ->set_is_sortable(true)
            ->add_callback([fundae_format::class, 'messagestostudents']);

        $columns[] = (new column(
            'messagestoteachers',
            new lang_string('messagestoteachers', 'report_fundae'),
            $this->get_entity_name()
        ))
            ->add_field('u.id', 'userid')
            ->add_field('c.id', 'courseid')
            ->set_is_sortable(true)
            ->add_callback([fundae_format::class, 'messagestoteachers']);

//        $columns[] = (new column(
//            'studentscontacted',
//            new lang_string('studentscontacted', 'report_fundae'),
//            $this->get_entity_name()
//        ))
//            ->add_field('u.id', 'userid')
//            ->add_field('c.id', 'courseid')
//            ->set_is_sortable(true)
//            ->add_callback([fundae_format::class, 'studentscontacted']);

//        $columns[] = (new column(
//            'teacherscontacted',
//            new lang_string('teacherscontacted', 'report_fundae'),
//            $this->get_entity_name()
//        ))
//            ->add_field('u.id', 'userid')
//            ->add_field('c.id', 'courseid')
//            ->set_is_sortable(true)
//            ->add_callback([fundae_format::class, 'teacherscontacted']);

        return $columns;
    }

    /**
     *
     * @return filter[]
     * @throws moodle_exception
     */
    protected function get_all_filters(): array {

        $filters[] = (new filter(
            date::class,
            'timeaccess',
            new lang_string('lastcourseaccess', 'report_fundae'),
            $this->get_entity_name(),
            "ula.timeaccess"
        ))
            ->add_joins($this->get_joins());

        return $filters;
    }
}
