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

namespace qbank_filtername;

use core\output\datafilter;
use core_question\local\bank\condition;
use core_question\local\bank\view;

/**
 * Defines filter name condition for question bank.
 *
 * @package    qbank_filtername
 * @author     Mateusz Grzeszczak <mateusz.grzeszczak@p.lodz.pl>
 * @author     Mateusz Walczak <mateusz.walczak@p.lodz.pl>
 * @copyright  2024 TUL E-Learning Center
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filter_name_condition extends condition {

    /** @var bool search questions in case sensitive mode or not */
    public bool $casesensitive;

    /** @var bool filter also question text or not */
    public bool $filtertext;

    /**
     * Constructor to initialize the filter name condition.
     *
     * @param view $qbank qbank view
     */
    public function __construct($qbank = null) {
        if (is_null($qbank)) {
            return;
        }
        parent::__construct($qbank);
        $this->casesensitive = $this->filter['filteroptions']['casesensitive'] ?? false;
        $this->filtertext = $this->filter['filteroptions']['filtertext'] ?? false;
    }

    /**
     * Build query from filter jointype, options and values.
     *
     * @param array $filter filter properties
     *
     * @return array where sql and params
     */
    public static function build_query_from_filter(array $filter): array {
        global $DB;

        $like = true;
        $inneroperator = 'AND';
        $outeroperator = 'AND';
        $filtertext = $filter['filteroptions']['filtertext'] ?? false;
        $casesensitive = $filter['filteroptions']['casesensitive'] ?? false;

        switch ($filter['jointype']) {
            case datafilter::JOINTYPE_NONE:
                $like = false;
                $inneroperator = 'AND';
                $outeroperator = 'AND';
                break;
            case datafilter::JOINTYPE_ANY:
                $like = true;
                $inneroperator = 'OR';
                $outeroperator = 'OR';
                break;
            case datafilter::JOINTYPE_ALL:
                $like = true;
                $inneroperator = 'OR';
                $outeroperator = 'AND';
                break;
        }

        $conditions = [];
        $params = [];

        foreach ($filter['values'] as $vid => $value) {
            $qnamecondition = $DB->sql_like('q.name', ':qname' . $vid, $casesensitive, notlike: !$like);
            if ($filtertext) {
                $qtextcondition = $DB->sql_like('q.questiontext', ':qtext' . $vid, $casesensitive, notlike: !$like);
                $conditionparts = [$qtextcondition, $inneroperator, $qnamecondition];
                $conditions[] = ' (' . implode(' ', $conditionparts) . ') ';
            } else {
                $conditions[] = ' (' . $qnamecondition . ') ';
            }

            $param = '%' . $DB->sql_like_escape($value) . '%';
            $params['qtext' . $vid] = $param;
            $params['qname' . $vid] = $param;
        }

        $where = implode($outeroperator, $conditions);

        return [$where, $params];
    }

    /**
     * Gets condition key.
     *
     * @return string
     */
    public static function get_condition_key() {
        return 'filtername';
    }

    /**
     * Gets condition title string.
     *
     * @return string
     */
    public function get_title() {
        return get_string('filtername', 'qbank_filtername');
    }

    /**
     * Gets condition amd filter class.
     *
     * @return string
     */
    public function get_filter_class() {
        return 'qbank_filtername/datafilter/filtertypes/name';
    }

    /**
     * Gets filter name options:
     * - casesensitive (bool) - by default true
     * - filtertext (bool) - by default false
     *
     * @return \stdClass
     */
    public function get_filteroptions(): \stdClass {
        return (object)[
            'casesensitive' => $this->casesensitive,
            'filtertext' => $this->filtertext,
        ];
    }

    /**
     * Allow custom set to true. Only custom values are expected.
     *
     * @return bool
     */
    public function allow_custom() {
        return true;
    }

    /**
     * Allow multiple set to true.
     *
     * @return bool
     */
    public function allow_multiple() {
        return true;
    }
}
