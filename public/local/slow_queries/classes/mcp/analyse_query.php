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
 * Provide read-only heuristic index suggestions for an existing log entry.
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_slow_queries\mcp;

use local_mcp\security\authenticated_identity;
use local_slow_queries\service\index_suggestion_service;
use local_slow_queries\service\table_schema_service;

/**
 * Never execute logged SQL, EXPLAIN ANALYZE, index DDL or arbitrary text from the MCP client.
 */
final class analyse_query extends read_base {
    /**
     * Return the unique MCP tool identifier.
     *
     * @return string
     */
    public function get_name(): string {
        return "slowqueries_analyse_query";
    }

    /**
     * Return the translated MCP tool title.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string("mcpanalysequery", "local_slow_queries");
    }

    /**
     * Return the translated MCP tool description.
     *
     * @return string
     */
    public function get_description(): string {
        return get_string("mcpanalysequerydesc", "local_slow_queries");
    }

    /**
     * Define the accepted MCP tool arguments.
     *
     * @return array
     */
    public function get_input_schema(): array {
        return $this->object_schema([
            "query_id" => ["type" => "integer", "minimum" => 1],
        ], ["query_id"]);
    }

    /**
     * Execute an authorized tool operation.
     *
     * @param array $arguments Input.
     * @param authenticated_identity $identity Administrator.
     * @return array
     */
    public function execute(array $arguments, authenticated_identity $identity): array {
        global $CFG;
        access::require_admin($identity);
        $row = report_service::query((int)$arguments["query_id"]);
        $tables = array_slice(table_schema_service::extract_tables((string)$row->sqltext), 0, 20);
        $suggestions = index_suggestion_service::suggest((string)$CFG->prefix, (string)$row->sqltext);
        $result = [];
        foreach (array_slice($suggestions, 0, 20) as $suggestion) {
            $result[] = [
                "table" => (string)($suggestion["table"] ?? ""),
                "columns" => array_slice((array)($suggestion["columns"] ?? []), 0, 8),
                "reason" => \core_text::substr((string)($suggestion["reason"] ?? ""), 0, 500),
                "candidate_ddl_not_executed" => \core_text::substr(
                    (string)($suggestion["create"] ?? ""), 0, 500
                ),
            ];
        }
        return [
            "query_id" => (int)$row->id,
            "fingerprint_sha256" => hash("sha256", (string)$row->sqltext),
            "tables" => $tables,
            "suggestions" => $result,
            "total_suggestions" => count($suggestions),
            "heuristic_only" => true,
            "ran_logged_sql" => false,
            "created_indexes" => false,
            "review_in_staging_before_any_change" => true,
        ];
    }
}
