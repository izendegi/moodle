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

/**
 * lib.php
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_slow_queries\check\performance\slow_queries;

/**
 * Adds the plugin entry to the global navigation.
 *
 * @param global_navigation $navigation The global navigation object.
 * @throws coding_exception
 * @throws dml_exception
 */
function local_slow_queries_extend_navigation_global(global_navigation $navigation): void {
    if (!isloggedin() || isguestuser()) {
        return;
    }

    $context = context_system::instance();
    if (!has_capability("moodle/site:config", $context)) {
        return;
    }

    $url = new moodle_url("/local/slow_queries/");
    $navigation->add(
        get_string("nav_index", "local_slow_queries"),
        $url,
        navigation_node::TYPE_CUSTOM,
        null,
        "local_slow_queries",
        new pix_icon("i/report", "")
    );
}

/**
 * Function local_slow_queries_performance_checks
 *
 * @return slow_queries[]
 */
function local_slow_queries_performance_checks() {
    return [
        new slow_queries(),
    ];
}
