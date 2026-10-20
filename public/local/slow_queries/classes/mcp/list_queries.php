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
 * List grouped slow SQL fingerprints in bounded pages.
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_slow_queries\mcp;

use local_mcp\security\authenticated_identity;

/**
 * Reuse repository grouping and limit the amount of SQL returned.
 */
final class list_queries extends read_base {
    /**
     * Return the unique MCP tool identifier.
     *
     * @return string
     */
    public function get_name(): string {
        return "slowqueries_list_queries";
    }

    /**
     * Return the translated MCP tool title.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string("mcplistqueries", "local_slow_queries");
    }

    /**
     * Return the translated MCP tool description.
     *
     * @return string
     */
    public function get_description(): string {
        return get_string("mcplistqueriesdesc", "local_slow_queries");
    }

    /**
     * Define the accepted MCP tool arguments.
     *
     * @return array
     */
    public function get_input_schema(): array {
        return $this->object_schema(query_filter::schema());
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
        return report_service::groups($arguments);
    }
}
