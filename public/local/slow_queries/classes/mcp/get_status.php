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
 * Report logged slow-query volume and configuration through MCP.
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_slow_queries\mcp;

use local_mcp\security\authenticated_identity;

/**
 * Expose only administrator-level site diagnostics.
 */
final class get_status extends read_base {
    /**
     * Return the unique MCP tool name.
     *
     * @return string
     */
    public function get_name(): string {
        return "slowqueries_get_status";
    }

    /**
     * Return the translated MCP tool title.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string("mcpgetstatus", "local_slow_queries");
    }

    /**
     * Return the translated MCP tool description.
     *
     * @return string
     */
    public function get_description(): string {
        return get_string("mcpgetstatusdesc", "local_slow_queries");
    }

    /**
     * Declare the input schema for the MCP tool.
     *
     * @return array
     */
    public function get_input_schema(): array {
        return $this->object_schema([
            "days" => ["type" => "integer", "minimum" => 1, "maximum" => 30],
        ]);
    }

    /**
     * Execute the authorized MCP operation.
     *
     * @param array $arguments Input.
     * @param authenticated_identity $identity Administrator.
     * @return array
     */
    public function execute(array $arguments, authenticated_identity $identity): array {
        access::require_admin($identity);
        return report_service::status((int)($arguments["days"] ?? 7));
    }
}
