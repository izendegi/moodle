<?php

declare(strict_types=1);

namespace paygw_mucommerce\local\entities;

use core_reportbuilder\local\filters\{date, duration, number, text};
use core_reportbuilder\local\report\{column, filter};
use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\helpers\format;
use lang_string;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/course/lib.php');

/**
 * Mucommerce entity class implementation
 *
 * This entity defines all the mucommerce columns and filters to be used in any report.
 *
 * @package     paygw_mucommerce
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mucommerce extends base {

    /**
     * Database tables that this entity uses and their default aliases
     *
     * @return array
     */
    protected function get_default_table_aliases(): array {
        return [
            'paygw_mucommerce' => 'mucom',
            'user' => 'muuser',
            'course' => 'mucourse',
            'enrol' => 'muenrol',
            'course_completion' => 'mucompletitions',
        ];
    }

    /**
     * The default title for this entity
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('mucommercereport', 'paygw_mucommerce');
    }

    /**
     * Initialise the entity, add all columns and filters
     *
     * @return base
     */
    public function initialise(): base {
        foreach ($this->get_all_columns() as $column) {
            $this->add_column($column);
        }
        foreach ($this->get_all_filters() as $filter) {
            $this->add_filter($filter);
        }
        foreach ($this->get_all_filters() as $condition) {
            $this->add_condition($condition);
        }
        return $this;
    }

    /**
     * Returns list of all available columns
     *
     * @return column[]
     */
    protected function get_all_columns(): array {
        $columns = [];
        $mucomalias = $this->get_table_alias('paygw_mucommerce');
        $join = $this->mucommercejoin();
        $columns[] = (new column(
            'mucom_orderid',
            new lang_string('orderid', 'paygw_mucommerce'),
            $this->get_entity_name()
        ))->set_is_sortable(true)->add_field("{$mucomalias}.mucom_orderid");

        $columns[] = (new column(
            'userid',
            new lang_string('userid', 'paygw_mucommerce'),
            $this->get_entity_name()
        ))->add_join($join)->set_is_sortable(true)->add_field("{$mucomalias}.userid");

        $columns[] = (new column(
            'itemid',
            new lang_string('itemid', 'paygw_mucommerce'),
            $this->get_entity_name()
        ))->add_join($join)->set_is_sortable(true)->add_field("{$mucomalias}.itemid");

        $columns[] = (new column(
            'courseid',
            new lang_string('courseid', 'paygw_mucommerce'),
            $this->get_entity_name()
        ))->add_join($join)->set_is_sortable(true)->add_field("{$mucomalias}.courseid");

        $columns[] = (new column(
            'is_paid',
            new lang_string('paymentstatus', 'paygw_mucommerce'),
            $this->get_entity_name()
        ))->set_is_sortable(true)->add_field("{$mucomalias}.is_paid");

        return $columns;
    }

    protected function get_all_filters(): array {
        $filters = [];
        $mucomalias = $this->get_table_alias('paygw_mucommerce');
        $join = $this->mucommercejoin();
        // Filtro para 'is_paid'
        $filters[] = (new filter(
            text::class,  // Mantener text::class porque is_paid es CHAR(1)
            'is_paid',
            new lang_string('filterispaid', 'paygw_mucommerce'),
            $this->get_entity_name(), "{$mucomalias}.is_paid"
        ));
        
        // Filtro para 'itemid'
        $filters[] = (new filter(
            number::class, 
            'itemid', 
            new lang_string('filteritemid', 'paygw_mucommerce'), 
            $this->get_entity_name(), "{$mucomalias}.itemid"
        ));

        return $filters;
    }    

/**
 * Define joins for the mucommerce table with user, course, and enrol tables
 *
 * @return string SQL JOIN statement
 */
public function mucommercejoin(): string {
    $mucomalias = $this->get_table_alias('paygw_mucommerce');
    $useralias = $this->get_table_alias('user');
    $coursealias = $this->get_table_alias('course');
    $enrolalias = $this->get_table_alias('enrol');

    return "
        JOIN {user} {$useralias} ON {$mucomalias}.userid = {$useralias}.id
        JOIN {course} {$coursealias} ON {$mucomalias}.courseid = {$coursealias}.id
        JOIN {enrol} {$enrolalias} ON {$mucomalias}.itemid = {$enrolalias}.id
    ";
}


}

