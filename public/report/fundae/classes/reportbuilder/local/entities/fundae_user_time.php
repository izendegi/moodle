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
use dml_exception;
use lang_string;
use report_fundae\reportbuilder\local\helpers\fundae_format;
use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\report\column;


/**
 * @package report_fundae
 * @author 3iPunt <https://www.tresipunt.com/>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 3iPunt <https://www.tresipunt.com/>
 */
class fundae_user_time extends base {

    /**
     * The default machine-readable name for this entity that will be used in the internal names of the columns/filters
     *
     * @return string
     */
    protected function get_default_entity_name(): string {
        return 'user_time';
    }

    /**
     * The default title for this entity in the list of columns/conditions/filters in the report builder
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('usertime', 'report_fundae');
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

        return $this;
    }

    /**
     * Database tables that this entity uses and their default aliases
     *
     * @return array
     */
    protected function get_default_table_aliases(): array {
        return [
            'logstore_standard_log' => 'lsl'
        ];
    }

    /**
     * Returns list of available columns
     *
     * @return column[]
     * @throws dml_exception
     * @throws coding_exception
     */
    protected function get_all_columns(): array {
        global $CFG;
        $columns = [];
        $columns[] = (new column(
            'timeoncourse',
            new lang_string('timeoncourse', 'report_fundae'),
            $this->get_entity_name()
        ))
            ->add_field('u.id', 'userid')
            ->add_field('c.id', 'courseid')
            ->set_is_sortable(false)
            ->add_callback([fundae_format::class, 'timeoncourse']);

        $columns[] = (new column(
            'timeactivities',
            new lang_string('timeactivities', 'report_fundae'),
            $this->get_entity_name()
        ))
            ->add_field('u.id', 'userid')
            ->add_field('c.id', 'courseid')
            ->set_is_sortable(false)
            ->add_callback([fundae_format::class, 'timeactivities']);

        $columns[] = (new column(
            'timescorms',
            new lang_string('timescorms', 'report_fundae'),
            $this->get_entity_name()
        ))
            ->add_field('u.id', 'userid')
            ->add_field('c.id', 'courseid')
            ->set_is_sortable(false)
            ->add_callback([fundae_format::class, 'timescorms']);

        if (file_exists($CFG->dirroot . '/mod/zoom/locallib.php')) {
            $columns[] = (new column(
                'timezoom',
                new lang_string('timezoom', 'report_fundae'),
                $this->get_entity_name()
            ))
                ->add_field('u.id', 'userid')
                ->add_field('c.id', 'courseid')
                ->set_is_sortable(false)
                ->add_callback([fundae_format::class, 'timezoom']);
        }

        if ((int)get_config('report_fundae', 'bbbreports') === 1) {
            $columns[] = (new column(
                'timebbb',
                new lang_string('timebbb', 'report_fundae'),
                $this->get_entity_name()
            ))
                ->add_field('u.id', 'userid')
                ->add_field('c.id', 'courseid')
                ->set_is_sortable(false)
                ->add_callback([fundae_format::class, 'timebbb']);
        }

        $columns[] = (new column(
            'numberofsessions',
            new lang_string('numberofsessions', 'report_fundae'),
            $this->get_entity_name()
        ))
            ->add_field('u.id', 'userid')
            ->add_field('c.id', 'courseid')
            ->set_is_sortable(false)
            ->add_callback([fundae_format::class, 'numberofsessions']);

        $columns[] = (new column(
            'daysonline',
            new lang_string('daysonline', 'report_fundae'),
            $this->get_entity_name()
        ))
            ->add_field('u.id', 'userid')
            ->add_field('c.id', 'courseid')
            ->set_is_sortable(false)
            ->add_callback([fundae_format::class, 'daysonline']);

        $columns[] = (new column(
            'ratioonline',
            new lang_string('ratioonline', 'report_fundae'),
            $this->get_entity_name()
        ))
            ->add_field('u.id', 'userid')
            ->add_field('c.id', 'courseid')
            ->set_is_sortable(false)
            ->add_callback([fundae_format::class, 'ratioonline']);

        return $columns;
    }

}
