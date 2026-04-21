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

declare(strict_types=1);

namespace paygw_mucommerce\reportbuilder\datasource;
use core_reportbuilder\datasource;
use core_reportbuilder\local\entities\course;
use core_reportbuilder\local\entities\user;
use core_enrol\reportbuilder\local\entities\enrol;
use core_course\reportbuilder\local\entities\completion;
/**
 * Mucommerce datasource
 *
 * @package   paygw_mucommerce
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mucommerce extends datasource {

    /**
     * Return user-friendly name of the datasource
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('pluginname', 'paygw_mucommerce');
    }

    /**
     * Initialise report
     */
    protected function initialise(): void {
        global $CFG;

        $mucommerceentity = new \paygw_mucommerce\local\entities\mucommerce();
        $mucomalias = $mucommerceentity->get_table_alias('paygw_mucommerce');

        $this->set_main_table('paygw_mucommerce', $mucomalias);
        $this->add_entity($mucommerceentity);

        $this->add_join($mucommerceentity->mucommercejoin());

        // Add core user join.
        $userentity = new user();
        $useralias = $userentity->get_table_alias('user');
        $userjoin = "JOIN {user} {$useralias} ON {$useralias}.id = {$mucomalias}.userid";
        $this->add_entity($userentity->add_join($userjoin));

        // Add core course join.
        $courseentity = new course();
        $coursealias = $courseentity->get_table_alias('course');
        $coursejoin = "JOIN {course} {$coursealias} ON {$coursealias}.id = {$mucomalias}.courseid";
        $this->add_entity($courseentity->add_join($coursejoin));

        $enrolentity = new enrol();
        $enrolalias = $enrolentity->get_table_alias('enrol');
        $enroljoin = "JOIN {enrol} {$enrolalias} ON {$enrolalias}.id = {$mucomalias}.itemid";
        $this->add_entity($enrolentity->add_join($enroljoin));
        
        $useralias = $userentity->get_table_alias('user');
        $coursealias = $courseentity->get_table_alias('course');

        // Añadir alias explícitamente.
        $completionentity = (new completion())
            ->set_table_aliases([
                'course' => $coursealias,
                'user' => $useralias,
            ]);
        $completionalias = $completionentity->get_table_alias('course_completion');
        $completionjoin = "LEFT JOIN {course_completions} {$completionalias} 
            ON {$completionalias}.userid = {$useralias}.id 
            AND {$completionalias}.course = {$coursealias}.id";
        $this->add_entity($completionentity->add_join($completionjoin));


        $this->add_all_from_entities();
    }

    /**
     * Return the columns that will be added to the report once it is created
     *
     * @return string[]
     */
    public function get_default_columns(): array {
        return [
            'course:fullname',
            'user:fullname',
            'mucommerce:mucom_orderid', // Corrected to match the database field
            'mucommerce:is_paid',
            'mucommerce:itemid'
        ];
    }

    /**
     * Return the filters that will be added to the report once it is created
     *
     * @return string[]
     */
    public function get_default_filters(): array {
        return [];
    }

    /**
     * Return the conditions that will be added to the report once it is created
     *
     * @return string[]
     */
    public function get_default_conditions(): array {
        return [];
    }
}
