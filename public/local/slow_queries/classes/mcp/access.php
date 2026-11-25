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
 * Authorize administrator-only MCP access to slow query records.
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_slow_queries\mcp;

use context_system;
use local_mcp\security\authenticated_identity;

/**
 * Slow SQL and backtraces can expose sensitive application data.
 */
final class access {
    /**
     * Enforce the same site configuration capability as the native report.
     *
     * @param authenticated_identity $identity Connected Moodle user.
     * @return void
     */
    public static function require_admin(authenticated_identity $identity): void {
        require_capability("moodle/site:config", context_system::instance(), $identity->userid);
    }
}
