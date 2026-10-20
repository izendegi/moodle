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
 * Read the recent executions of an identical logged slow SQL statement.
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_slow_queries\mcp;

use local_mcp\security\authenticated_identity;

/**
 * Show only performance values, never parameters from individual executions.
 */
final class get_occurrences extends read_base {
    /**
     * Return the unique MCP tool identifier.
     *
     * @return string
     */
    public function get_name(): string {
        return "slowqueries_get_occurrences";
    }

    /**
     * Return the translated MCP tool title.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string("mcpgetoccurrences", "local_slow_queries");
    }

    /**
     * Return the translated MCP tool description.
     *
     * @return string
     */
    public function get_description(): string {
        return get_string("mcpgetoccurrencesdesc", "local_slow_queries");
    }

    /**
     * Define the accepted MCP tool arguments.
     *
     * @return array
     */
    public function get_input_schema(): array {
        return $this->object_schema([
            "query_id" => ["type" => "integer", "minimum" => 1],
            "days" => ["type" => "integer", "minimum" => 1, "maximum" => 30],
            "offset" => ["type" => "integer", "minimum" => 0, "maximum" => 100000],
            "limit" => ["type" => "integer", "minimum" => 1, "maximum" => 40],
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
        return report_service::occurrences($row, (int)($arguments["days"] ?? 7),
            (int)($arguments["offset"] ?? 0), (int)($arguments["limit"] ?? 20));
    }
}
