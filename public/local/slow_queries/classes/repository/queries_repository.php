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
 * queries_repository.php
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_slow_queries\repository;

use dml_exception;
use stdClass;

/**
 * Data access layer for the log_queries table.
 */
class queries_repository {
    /**
     * Gets a row by id.
     *
     * @param int $id Row id.
     * @return stdClass Row.
     * @throws dml_exception
     */
    public function get_by_id(int $id): stdClass {
        global $DB;

        $querie = $DB->get_record("log_queries", ["id" => $id], "*", MUST_EXIST);
        $sql = "
            SELECT AVG(exectime)
              FROM {log_queries}
             WHERE sqltext = :sqltext
          GROUP BY sqltext";
        $querie->avgtime = $DB->get_field_sql($sql, ["sqltext" => $querie->sqltext]);
        $querie->avgtime = number_format($querie->avgtime, 2);

        $sql = "
            SELECT comments
              FROM {local_slow_queries_comments}
             WHERE sqltext = :sqltext";
        $querie->comments = $DB->get_field_sql($sql, ["sqltext" => $querie->sqltext]);

        return $querie;
    }

    /**
     * Counts grouped rows for the home table.
     *
     * @param string $search SQL search string.
     * @param float $minexec Minimum exec time.
     * @return int Count of grouped rows.
     * @throws dml_exception
     */
    public function count_grouped_filtered(string $search, float $minexec): int {
        global $DB;

        [$where, $params] = $this->build_where($search, $minexec);

        $sql = "
            SELECT COUNT(1)
              FROM (
                    SELECT 1
                      FROM {log_queries}
                     WHERE {$where}
                  GROUP BY sqltext
                   ) x";
        return $DB->count_records_sql($sql, $params);
    }

    /**
     * Builds the FROM clause for the home table (grouped by sqltext).
     *
     * Output fields from the derived table:
     * - id (sample id: MAX(id))
     * - sqltext
     * - backtrace (from sample row)
     * - cnt (COUNT)
     * - avgtime (AVG(exectime))
     * - iscron (MAX(CASE WHEN backtrace LIKE cron THEN 1 ELSE 0 END))
     *
     * @param string $search SQL search string.
     * @param float $minexec Minimum exec time.
     * @return array [fromsql, params]
     */
    public function get_grouped_from_for_table(string $search, float $minexec): array {
        global $DB;

        [$where, $params] = $this->build_where($search, $minexec);

        $cronlike = $DB->sql_like("backtrace", ":cronlike", false, false);
        $params["cronlike"] = "%/admin/cli/cron.php%";

        $inner = "
            SELECT sqltext,
                   COUNT(1) AS cnt,
                   AVG(exectime) AS avgtime,
                   MAX(id) AS sampleid,
                   MAX(CASE WHEN {$cronlike} THEN 1 ELSE 0 END) AS iscron
              FROM {log_queries}
             WHERE {$where}
          GROUP BY sqltext";

        $outer = "
            SELECT
                agg.sampleid AS id,
                agg.sqltext,
                q.backtrace,
                agg.cnt,
                agg.avgtime,
                agg.iscron
              FROM ({$inner}) agg
              JOIN {log_queries} q
                ON q.id = agg.sampleid";

        return ["({$outer}) lsq", $params];
    }

    /**
     * Builds WHERE clause used by both grouped and raw queries.
     *
     * @param string $search Search term.
     * @param float $minexec Minimum exec time.
     * @return array [where, params]
     */
    private function build_where(string $search, float $minexec): array {
        global $DB;

        $wheres = [];
        $params = [];

        $wheres[] = "exectime >= :minexec";
        $params["minexec"] = $minexec;

        $search = trim($search);
        if ($search !== "") {
            $wheres[] = $DB->sql_like("sqltext", ":search", false, false);
            $params["search"] = "%{$DB->sql_like_escape($search)}%";
        }

        return [implode(" AND ", $wheres), $params];
    }

    /**
     * Loads rows for a given SQL (best-effort LIKE match) in a time period.
     *
     * @param int $from Unix timestamp (inclusive).
     * @param int $to Unix timestamp (exclusive).
     * @param string $sql SQL text to match.
     * @return array List of rows (id, timelogged, exectime).
     * @throws dml_exception
     */
    public function get_for_sql_like_period(int $from, int $to, string $sql): array {
        global $DB;

        $query = "
            SELECT id, timelogged, exectime
              FROM {log_queries}
             WHERE timelogged >= :from
               AND timelogged <  :to
               AND sqltext    =  :sqltext
          ORDER BY timelogged ASC";

        return $DB->get_records_sql($query, [
            "from" => $from,
            "to" => $to,
            "sqltext" => $sql,
        ]);
    }
}
