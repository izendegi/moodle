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
 * backtrace_service.php
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_slow_queries\service;

/**
 * Utilities to interpret backtraces stored in log_queries.
 */
class backtrace_service {
    /**
     * Returns true when the backtrace indicates a CRON execution.
     *
     * @param string|null $backtrace Backtrace text.
     * @return bool True for CRON.
     */
    public static function is_cron(?string $backtrace): bool {
        if (empty($backtrace)) {
            return false;
        }
        return (strpos($backtrace, "admin/cli/cron.php") !== false);
    }

    /**
     * Extracts the most useful "origin" line: the first stack line after lib/dml/.
     *
     * @param string|null $backtrace Backtrace text.
     * @return string A single trimmed line for display.
     */
    public static function get_origin_line(?string $backtrace): string {
        if (empty($backtrace)) {
            return "-";
        }

        $lines = preg_split("/\r\n|\n|\r/", $backtrace);
        $lines = array_values(array_filter(array_map("trim", $lines)));

        $dmlindex = 0;
        foreach ($lines as $i => $line) {
            if (strpos($line, "lib/dml") === false) {
                $dmlindex = $i;
                break;
            }
        }

        $candidate = null;
        if ($dmlindex !== null && isset($lines[$dmlindex + 2])) {
            $candidate = $lines[$dmlindex + 1];
        } else if (!empty($lines)) {
            $candidate = $lines[0];
        }

        $candidate = preg_replace('/^\*\s*/', "", $candidate);
        $candidate = preg_replace('/^(line\s+\d+\s+of\s+[^:]+):.*$/', '$1', $candidate);
        return $candidate;
    }
}
