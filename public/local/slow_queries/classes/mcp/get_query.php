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
 * Fetch one slow-query record with explicitly requested sensitive fields.
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_slow_queries\mcp;

use local_mcp\security\authenticated_identity;

/**
 * SQL, bind values and backtraces are only released through opt-in switches.
 */
final class get_query extends read_base {
    /**
     * Return the unique MCP tool identifier.
     *
     * @return string
     */
    public function get_name(): string {
        return "slowqueries_get_query";
    }

    /**
     * Return the translated MCP tool title.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string("mcpgetquery", "local_slow_queries");
    }

    /**
     * Return the translated MCP tool description.
     *
     * @return string
     */
    public function get_description(): string {
        return get_string("mcpgetquerydesc", "local_slow_queries");
    }

    /**
     * Define the accepted MCP tool arguments.
     *
     * @return array
     */
    public function get_input_schema(): array {
        return $this->object_schema([
            "query_id" => ["type" => "integer", "minimum" => 1],
            "include_sql" => ["type" => "boolean"],
            "include_sensitive" => ["type" => "boolean",
                "description" => "Explicit opt-in to raw bind parameters and full backtrace."],
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
        access::require_admin($identity);
        $row = report_service::query((int)$arguments["query_id"]);
        return report_service::query_data($row, !empty($arguments["include_sql"]),
            !empty($arguments["include_sensitive"])) + [
                "comment_state_hash" => comment_service::state_hash((string)$row->sqltext),
            ];
    }
}
