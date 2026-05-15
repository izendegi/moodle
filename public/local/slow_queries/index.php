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
 * index.php
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . "/../../config.php");
require_once("{$CFG->libdir}/tablelib.php");
require_once($CFG->libdir.'/adminlib.php');

use local_slow_queries\check\dboptions;
use local_slow_queries\repository\queries_repository;
use local_slow_queries\table\home_table;

admin_externalpage_setup('localslowqueries', '', null, '', ['pagelayout' => 'report']);

$PAGE->set_url(new moodle_url("/local/slow_queries/"));
$PAGE->set_title(get_string("index_title", "local_slow_queries"));
$PAGE->set_heading(get_string("index_title", "local_slow_queries"));
$PAGE->add_body_class("local-slow-queries");

$repo = new queries_repository();

echo $OUTPUT->header();

// Filters.
$search = optional_param("search", "", PARAM_RAW_TRIMMED);
$minexec = optional_param("minexec", 3, PARAM_FLOAT);

echo $OUTPUT->render_from_template("local_slow_queries/install", dboptions::test_logslow());

$template = [
    "reporturl" => (new moodle_url("/local/slow_queries/report.php"))->out(false),
    "search" => $search,
    "minexec" => $minexec,
];
echo $OUTPUT->render_from_template("local_slow_queries/index", $template);

// Grouped table: GROUP BY sqltext, showing COUNT(*) and AVG(exectime).
$table = new home_table("local_slow_queries_home", $PAGE->url);

$total = $repo->count_grouped_filtered($search, $minexec);
$table->pagesize(30, $total);

[$from, $params] = $repo->get_grouped_from_for_table($search, $minexec);
$table->set_sql("id, sqltext, backtrace, cnt, avgtime, iscron", $from, "1=1", $params);

ob_start();
$table->out(30, true);
$tablehtml = ob_get_clean();

echo html_writer::div($tablehtml, "card mt-3");

echo $OUTPUT->footer();
