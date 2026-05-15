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
 * phpcs:disable moodle.Strings.ForbiddenStrings.Found
 * detail.php
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . "/../../config.php");
require_once($CFG->libdir.'/adminlib.php');


use local_slow_queries\repository\queries_repository;
use local_slow_queries\service\backtrace_service;
use local_slow_queries\service\explain_service;
use local_slow_queries\service\index_suggestion_service;
use local_slow_queries\service\sql_params_service;
use local_slow_queries\service\table_schema_service;
use local_slow_queries\service\timeline_service;

admin_externalpage_setup('localslowqueries', '', null, '', ['pagelayout' => 'report']);

$id = required_param("id", PARAM_INT);

$PAGE->set_url(new moodle_url("/local/slow_queries/detail.php", ["id" => $id]));
$PAGE->set_title(get_string("detail_title", "local_slow_queries"));
$PAGE->set_heading(get_string("detail_title", "local_slow_queries"));
$PAGE->add_body_class("local-slow-queries");

$repo = new queries_repository();
$querie = $repo->get_by_id($id);

$commenttext = optional_param("comments", false, PARAM_TEXT);
if ($commenttext) {
    $sql = "
            SELECT *
              FROM {local_slow_queries_comments}
             WHERE sqltext = :sqltext";
    $comments = $DB->get_record_sql($sql, ["sqltext" => $querie->sqltext]);
    if ($comments) {
        $comments->comments = $commenttext;
        $comments->timemodified = time();
        $DB->update_record("local_slow_queries_comments", $comments);
    } else {
        $comments = (object) [
            "sqltext" => $querie->sqltext,
            "comments" => $commenttext,
            "timemodified" => time(),
        ];
        $DB->insert_record("local_slow_queries_comments", $comments);
    }
    redirect(new moodle_url("/local/slow_queries/detail.php", ["id" => $id]), get_string("changessaved"));
}

$params = sql_params_service::parse_params($querie->sqlparams ?? "");
$expanded = sql_params_service::expand_sql($querie->sqltext, $params);
$paramsblock = sql_params_service::format_params_block($params);

$iscron = backtrace_service::is_cron($querie->backtrace ?? "");

$tables = table_schema_service::extract_tables($querie->sqltext);
$schemablock = table_schema_service::build_schema_block($CFG->prefix, $tables);

// Automatic index suggestions.
$suggestions = index_suggestion_service::suggest($CFG->prefix, $querie->sqltext);

$suggestionsview = [];
foreach ($suggestions as $s) {
    $suggestionsview[] = [
        "table" => $s["table"],
        "columns" => implode(", ", (array) $s["columns"]),
        "reason" => $s["reason"],
        "create" => $s["create"],
    ];
}

$template = [
    "backurl" => (new moodle_url("/local/slow_queries/"))->out(false),
    "timelogged" => userdate($querie->timelogged),
    "comments" => $querie->comments,
    "exectime" => format_float($querie->exectime, 5),
    "iscron" => $iscron,
    "sqltext" => $querie->sqltext,
    "paramsblock" => $paramsblock,
    "origin" => $querie->backtrace,
    "expandedsql" => $expanded,
    "hassuggestions" => !empty($suggestionsview),
    "suggestions" => $suggestionsview,
    "timeline_html" => timeline_service::create_html($querie, $repo),
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template("local_slow_queries/detail", $template);

$prompt = [];
$prompt[] = "The SQL query below is very slow (~{$querie->avgtime}s). ";
$prompt[] = "Analyze the execution plan and propose realistic index optimizations for Moodle ({$DB->get_dbfamily()}), " .
    "explaining the expected impact and possible trade-offs.\n";
$prompt[] = "# SQL:\n```SQL\n{$expanded}\n```\n";
$prompt[] = "# Tables involved (metadata):\n{$schemablock}\n";
$explainsql = explain_service::explain_to_markdown($expanded);
if ($explainsql) {
    $prompt[] = "# EXPLAIN Statement:\n{$explainsql}\n";
}
$prompt[] = "# Backtrace origin:\n{$querie->backtrace}";
$prompt[] = "# Return the explanation in " . ($SESSION->lang ?? $USER->lang);
echo $OUTPUT->render_from_template("local_slow_queries/detail_prompt", [
    "prompt" => implode("\n", $prompt),
]);
echo $OUTPUT->footer();
