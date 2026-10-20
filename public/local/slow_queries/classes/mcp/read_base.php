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
 * Common system context and administrator capability for MCP READ.
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_slow_queries\mcp;

use context;
use context_system;
use local_mcp\read\tool\base_tool;

/**
 * Resolve a system context for sensitive SQL diagnostics.
 */
abstract class read_base extends base_tool {
    /**
     * Return the required administrator capability.
     *
     * @return string
     */
    public function get_required_capability(): string {
        return "moodle/site:config";
    }

    /**
     * Resolve the system context for capability enforcement.
     *
     * @param array $arguments Input.
     * @return context
     */
    public function resolve_context(array $arguments): context {
        return context_system::instance();
    }
}
