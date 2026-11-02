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
 * Validate bounded slow-query MCP search and time filters.
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_slow_queries\mcp;

use moodle_exception;

/**
 * Keep admin reports bounded even when logging volumes are large.
 */
final class query_filter {
    /** Maximum rows per MCP response. */
    public const MAX_LIMIT = 40;

    /**
     * Declare a bounded search filter schema.
     *
     * @return array
     */
    public static function schema(): array {
        return [
            "min_seconds" => ["type" => "number", "minimum" => 0, "maximum" => 86400],
            "search" => ["type" => "string", "maxLength" => 200],
            "offset" => ["type" => "integer", "minimum" => 0, "maximum" => 100000],
            "limit" => ["type" => "integer", "minimum" => 1, "maximum" => self::MAX_LIMIT],
            "include_sql" => ["type" => "boolean"],
        ];
    }

    /**
     * Normalize and validate search filters.
     *
     * @param array $arguments Tool arguments.
     * @return array{string, float, int, int, bool}
     */
    public static function parse(array $arguments): array {
        $search = trim((string)($arguments["search"] ?? ""));
        $seconds = (float)($arguments["min_seconds"] ?? 3.0);
        $offset = (int)($arguments["offset"] ?? 0);
        $limit = (int)($arguments["limit"] ?? 20);
        if (strlen($search) > 200 || !is_finite($seconds) ||
                $seconds < 0 || $seconds > 86400 || $offset < 0 ||
                $offset > 100000 || $limit < 1 || $limit > self::MAX_LIMIT) {
            throw new moodle_exception("mcpinvalidfilter", "local_slow_queries");
        }
        return [$search, $seconds, $offset, $limit, !empty($arguments["include_sql"])];
    }
}
