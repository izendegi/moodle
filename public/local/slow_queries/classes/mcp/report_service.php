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
 * Read bounded query summaries and logs using the plugin repository.
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_slow_queries\mcp;

use local_slow_queries\check\dboptions;
use local_slow_queries\repository\queries_repository;
use local_slow_queries\service\backtrace_service;
use moodle_exception;
use stdClass;

/**
 * Read-only projections; never re-execute the SQL captured in a log.
 */
final class report_service {
    /**
     * Check availability of the SQL logging table.
     *
     * @return bool
     */
    public static function log_table_exists(): bool {
        global $DB;
        return $DB->get_manager()->table_exists("log_queries");
    }

    /**
     * Require logged queries to be available.
     *
     * @return void
     */
    public static function require_log_table(): void {
        if (!self::log_table_exists()) {
            throw new moodle_exception("mcplogunavailable", "local_slow_queries");
        }
    }

    /**
     * Return aggregate metrics for a bounded recent time interval.
     *
     * @param int $days Time window.
     * @return array
     */
    public static function status(int $days): array {
        global $DB;
        if ($days < 1 || $days > 30) {
            throw new moodle_exception("mcpinvalidwindow", "local_slow_queries");
        }
        $config = dboptions::test_logslow();
        $exists = self::log_table_exists();
        $result = [
            "log_table_present" => $exists,
            "logslow_configured" => (bool)$config["showlogslowwarning"],
            "logslow_value" => (string)$config["logslowvalue"],
            "window_days" => $days,
            "read_only" => true,
        ];
        if (!$exists) {
            return $result + ["query_count" => 0, "errors" => 0, "avg_seconds" => null,
                "max_seconds" => null];
        }
        $row = $DB->get_record_sql(
            "SELECT COUNT(1) AS total,
                    SUM(CASE WHEN error <> 0 THEN 1 ELSE 0 END) AS errors,
                    AVG(exectime) AS avgtime,
                    MAX(exectime) AS maxtime
               FROM {log_queries}
              WHERE timelogged >= :since",
            ["since" => time() - $days * DAYSECS]
        );
        return $result + [
            "query_count" => (int)($row->total ?? 0),
            "errors" => (int)($row->errors ?? 0),
            "avg_seconds" => $row->avgtime === null ? null : (float)$row->avgtime,
            "max_seconds" => $row->maxtime === null ? null : (float)$row->maxtime,
        ];
    }

    /**
     * List groups of identical SQL statements using the existing repository.
     *
     * @param array $arguments Tool arguments.
     * @return array
     */
    public static function groups(array $arguments): array {
        global $DB;
        self::require_log_table();
        [$search, $seconds, $offset, $limit, $includesql] = query_filter::parse($arguments);
        $repository = new queries_repository();
        $total = $repository->count_grouped_filtered($search, $seconds);
        [$from, $params] = $repository->get_grouped_from_for_table($search, $seconds);
        $sql = "SELECT lsq.id, lsq.sqltext, lsq.backtrace, lsq.cnt, lsq.avgtime, lsq.iscron
                  FROM {$from}
              ORDER BY lsq.avgtime DESC, lsq.id DESC";
        $records = $DB->get_records_sql($sql, $params, $offset, $limit);
        $groups = [];
        foreach ($records as $row) {
            $group = [
                "sample_query_id" => (int)$row->id,
                "fingerprint_sha256" => hash("sha256", (string)$row->sqltext),
                "executions" => (int)$row->cnt,
                "avg_seconds" => (float)$row->avgtime,
                "is_cron" => (bool)$row->iscron,
                "origin" => \core_text::substr(
                    (string)backtrace_service::get_origin_line($row->backtrace), 0, 250
                ),
            ];
            if ($includesql) {
                $group["sql_preview"] = \core_text::substr((string)$row->sqltext, 0, 500);
                $group["sql_truncated"] = \core_text::strlen((string)$row->sqltext) > 500;
            }
            $groups[] = $group;
        }
        return [
            "total_groups" => $total,
            "offset" => $offset,
            "limit" => $limit,
            "has_more" => $offset + count($groups) < $total,
            "minimum_seconds" => $seconds,
            "groups" => $groups,
        ];
    }

    /**
     * Resolve an actual query row through the plugin's existing repository.
     *
     * @param int $id Logged query ID.
     * @return stdClass
     */
    public static function query(int $id): stdClass {
        self::require_log_table();
        if ($id < 1) {
            throw new moodle_exception("mcpinvalidquery", "local_slow_queries");
        }
        return (new queries_repository())->get_by_id($id);
    }

    /**
     * Return a bounded, permission-sensitive projection.
     *
     * @param stdClass $row Logged record.
     * @param bool $includesql Expose SQL text if explicitly requested.
     * @param bool $includesensitive Expose parameters and raw backtrace.
     * @return array
     */
    public static function query_data(
        stdClass $row, bool $includesql = false, bool $includesensitive = false
    ): array {
        $result = [
            "query_id" => (int)$row->id,
            "fingerprint_sha256" => hash("sha256", (string)$row->sqltext),
            "type" => (string)$row->qtype,
            "execution_seconds" => (float)$row->exectime,
            "average_seconds" => (float)$row->avgtime,
            "logged_at" => (int)$row->timelogged,
            "has_error" => !empty($row->error),
            "is_cron" => backtrace_service::is_cron((string)$row->backtrace),
            "origin" => \core_text::substr(
                (string)backtrace_service::get_origin_line($row->backtrace), 0, 250
            ),
            "comments" => \core_text::substr((string)($row->comments ?? ""), 0, 5000),
            "raw_sql_included" => $includesql,
            "sensitive_fields_included" => $includesensitive,
        ];
        if ($includesql) {
            $result["sql"] = \core_text::substr((string)$row->sqltext, 0, 12000);
            $result["sql_truncated"] = \core_text::strlen((string)$row->sqltext) > 12000;
        }
        if ($includesensitive) {
            $result["sqlparams"] = \core_text::substr((string)$row->sqlparams, 0, 6000);
            $result["backtrace"] = \core_text::substr((string)$row->backtrace, 0, 6000);
            $result["sensitive_truncated"] = \core_text::strlen((string)$row->sqlparams) > 6000 ||
                \core_text::strlen((string)$row->backtrace) > 6000;
        }
        return $result;
    }

    /**
     * Return bounded log occurrences of the exact SQL text in a time period.
     *
     * @param stdClass $record Sample.
     * @param int $days Days (1-30).
     * @param int $offset Page offset.
     * @param int $limit Max 40 rows.
     * @return array
     */
    public static function occurrences(stdClass $record, int $days, int $offset, int $limit): array {
        global $DB;
        if ($days < 1 || $days > 30 || $offset < 0 || $offset > 100000 ||
                $limit < 1 || $limit > query_filter::MAX_LIMIT) {
            throw new moodle_exception("mcpinvalidwindow", "local_slow_queries");
        }
        $where = "sqltext = :sqltext AND timelogged >= :since";
        $params = ["sqltext" => $record->sqltext, "since" => time() - $days * DAYSECS];
        $total = $DB->count_records_select("log_queries", $where, $params);
        $rows = $DB->get_records_select("log_queries", $where, $params,
            "timelogged DESC, id DESC", "id,exectime,timelogged,error,qtype", $offset, $limit);
        $occurrences = [];
        foreach ($rows as $row) {
            $occurrences[] = [
                "query_id" => (int)$row->id,
                "logged_at" => (int)$row->timelogged,
                "seconds" => (float)$row->exectime,
                "has_error" => !empty($row->error),
                "type" => (string)$row->qtype,
            ];
        }
        return [
            "sample_query_id" => (int)$record->id,
            "window_days" => $days,
            "total" => $total,
            "offset" => $offset,
            "limit" => $limit,
            "has_more" => $offset + count($occurrences) < $total,
            "occurrences" => $occurrences,
        ];
    }
}
