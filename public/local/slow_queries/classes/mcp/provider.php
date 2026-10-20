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
 * Optional MCP provider for administrator slow query diagnostics.
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_slow_queries\mcp;

use local_mcp\extension\read_provider_interface;
use local_mcp\extension\write_provider_interface;

/**
 * Auto-discovered MCP tools for existing logged slow queries.
 */
final class provider implements read_provider_interface, write_provider_interface {
    /**
     * Register the diagnostic read tools.
     *
     * @return array
     */
    public function get_read_tools(): array {
        return [
            new get_status(),
            new list_queries(),
            new get_query(),
            new analyse_query(),
            new get_occurrences(),
        ];
    }

    /**
     * Register the confirmed write tools.
     *
     * @return array
     */
    public function get_write_tools(): array {
        return [new save_comment()];
    }
}
