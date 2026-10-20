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
 * local_slow_queries.php
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['checkperformance_slowqueries_action'] = 'View slow queries';
$string['checkperformance_slowqueries_details_found'] = 'Found <strong>{$a->count}</strong> queries that took more than <strong>{$a->seconds}s</strong> - see <a href="{$a->url}">Slow queries report</a>';
$string['checkperformance_slowqueries_details_morethan'] = 'There are <strong>{$a->count}</strong> queries over <strong>{$a->seconds}s</strong> - see <a href="{$a->url}">Slow queries report</a>';
$string['checkperformance_slowqueries_name'] = 'Slow queries affecting your Moodle performance';
$string['checkperformance_slowqueries_none'] = 'No query took longer than 5s.';
$string['checkperformance_slowqueries_notconfigured'] = 'Not configured';
$string['checkperformance_slowqueries_summary_found'] = 'Found <strong>{$a->count}</strong> queries that took more than {$a->seconds}s';
$string['col_avgtime'] = 'Avg time (s)';
$string['col_count'] = 'Count';
$string['col_cron'] = 'CRON';
$string['col_origin'] = 'Backtrace';
$string['col_parameters'] = 'Parameters';
$string['col_sqlpreview'] = 'SQL';
$string['comments_title'] = 'Comments';
$string['detail_indexes'] = 'Possible missing indexes';
$string['detail_indexes_none'] = 'No index suggestions detected for this query.';
$string['detail_indexes_notice'] = 'Suggestions are heuristic. Test carefully on a staging environment and validate with EXPLAIN/ANALYZE.';
$string['detail_sql'] = 'SQL and parameters';
$string['detail_sql_expanded'] = 'SQL with parameters';
$string['detail_sql_expanded_desc'] = 'Best-effort expansion for analysis (display only)';
$string['detail_title'] = 'Query details';
$string['duration_days'] = '{$a} day(s)';
$string['duration_hours'] = '{$a} hour(s)';
$string['duration_minutes'] = '{$a} minute(s)';
$string['duration_seconds'] = '{$a} second(s)';
$string['filter_apply'] = 'Apply';
$string['filter_minexec'] = 'Min exec time (s)';
$string['filter_search'] = 'Search SQL';
$string['filter_search_ph'] = 'Type part of the SQL to search...';
$string['filter_title'] = 'Filters';
$string['index_title'] = 'Slow queries';
$string['logslow_warning_body'] = 'This page reads from <code>mdl_log_queries</code>, but your site is not configured to log slow SQL queries. Enable <code>logslow</code> in <code>config.php</code> (set to <code>true</code> or a number in seconds). Example:';
$string['logslow_warning_current'] = 'Current value';
$string['logslow_warning_hint'] = 'After saving <code>config.php</code>, reproduce the slow page/cron task and then refresh this page to see new entries.';
$string['logslow_warning_title'] = 'Slow query logging is disabled';
$string["mcp_banner_badge"] = "AI INTEGRATION";
$string["mcp_banner_benefit_control"] = "Confirmation required for write actions";
$string["mcp_banner_benefit_discovery"] = "Automatic tool discovery";
$string["mcp_banner_benefit_security"] = "Moodle permissions enforced";
$string["mcp_banner_cta"] = "Explore Moodle MCP";
$string["mcp_banner_description"] = "Install local_mcp to expose Slow Queries features to ChatGPT through native Moodle permissions.";
$string["mcp_banner_dismiss"] = "Do not show this again";
$string["mcp_banner_title"] = "Bring Slow Queries into ChatGPT";
$string["mcpanalysequery"] = "Analyze a logged slow SQL statement";
$string["mcpanalysequerydesc"] = "Read-only heuristic index suggestions and involved tables for one logged statement; never runs its SQL or creates indexes.";
$string["mcpcommentbusy"] = "Another administrator is updating this query comment.";
$string["mcpduplicatecomments"] = "Multiple investigation comments exist for this SQL statement; resolve duplicates before editing.";
$string["mcpgetoccurrences"] = "Get slow SQL execution history";
$string["mcpgetoccurrencesdesc"] = "Read a paginated list of recent execution durations for a selected logged SQL statement, without exposing bind values.";
$string["mcpgetquery"] = "Get logged slow SQL details";
$string["mcpgetquerydesc"] = "Read one logged SQL statement and comment. SQL text, bound parameters and backtrace require explicit opt-in flags; administrator-only.";
$string["mcpgetstatus"] = "Get slow SQL logging status";
$string["mcpgetstatusdesc"] = "Read logging configuration and aggregate performance counts for the last one to thirty days, without changing configuration.";
$string["mcpinvalidcomment"] = "The investigation comment exceeds the permitted length.";
$string["mcpinvalidfilter"] = "Invalid or excessive SQL filter, offset or page length.";
$string["mcpinvalidquery"] = "Enter an existing positive logged query ID.";
$string["mcpinvalidwindow"] = "Choose a window of one to thirty days and a page length of at most forty.";
$string["mcplistqueries"] = "List grouped slow SQL statements";
$string["mcplistqueriesdesc"] = "Search grouped slow SQL fingerprints with bounded pagination; return raw SQL previews only when explicitly requested.";
$string["mcplogunavailable"] = "The slow query log table is not available. Check the Moodle database logging configuration.";
$string["mcpsavecomment"] = "Save a slow SQL investigation comment";
$string["mcpsavecommentdesc"] = "After explicit confirmation, create or update the comment shared by identical logged SQL statements; never executes logged SQL.";
$string["mcpstalecomment"] = "The investigation comment changed. Read the query again to obtain an updated state hash.";
$string['nav_index'] = 'Slow queries';
$string['pluginname'] = 'Slow queries';
$string['privacy:metadata'] = 'The Slow queries plugin does not store any personal data. It only displays existing database query log records to administrators.';
$string['timeline_calendar'] = 'Calendar timeline';
$string['timeline_last_7'] = '<strong>{$a}</strong> executions in the last 7 days';
$string['timeline_scale'] = 'The scale corresponds to 1 second for {$a} pixels';
$string['timeline_title'] = 'Queries timeline';
$string['timeline_totaltime'] = 'In total, the database spent <strong>{$a}</strong> executing these SQL statements';
