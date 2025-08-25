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

use core_question\local\bank\plugin_features_base;
use core_question\local\bank\view;

/**
 * Defines plugin features.
 *
 * @package    qbank_filtername
 * @author     Mateusz Grzeszczak <mateusz.grzeszczak@p.lodz.pl>
 * @author     Mateusz Walczak <mateusz.walczak@p.lodz.pl>
 * @copyright  2024 TUL E-Learning Center
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plugin_feature extends plugin_features_base {

    /**
     * Define the only plugin feature which is filter.
     *
     * @param view|null $qbank
     *
     * @return array
     */
    public function get_question_filters(view $qbank = null): array {
        return [
            new filter_name_condition($qbank),
        ];
    }
}
