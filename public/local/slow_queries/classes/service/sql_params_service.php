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
 * sql_params_service.php
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_slow_queries\service;

/**
 * Parses and formats SQL parameters stored as a string and expands placeholders in SQL.
 */
class sql_params_service {
    /**
     * Parses a parameter string that looks like a PHP var_export array:
     * "array ( 0 => 123, 1 => 'abc', )".
     *
     * @param string|null $raw Raw params.
     * @return array Parsed list (0-based).
     */
    public static function parse_params(?string $raw): array {
        if (empty($raw)) {
            return [];
        }

        $raw = trim($raw);

        // If it's JSON, prefer JSON.
        if ((strpos($raw, "{") == 0 || strpos($raw, "[") == 0)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return array_values($decoded);
            }
        }

        // Minimal, safe parser for "array ( 0 => X, 1 => Y, )" style.
        if (stripos($raw, "array") === false) {
            return [];
        }

        $lines = preg_split("/\r\n|\n|\r/", $raw);
        $items = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line == "" || $line == "array (" || $line == ")" || $line == "array(") {
                continue;
            }

            if (!preg_match('/^\s*(\d+)\s*=>\s*(.+?)\s*,?\s*$/', $line, $m)) {
                continue;
            }

            $idx = $m[1];
            $valraw = trim($m[2]);

            $items[$idx] = self::parse_scalar($valraw);
        }

        ksort($items);
        return array_values($items);
    }

    /**
     * Expands SQL placeholders '?' with parsed parameters (best-effort).
     * It avoids replacing question marks inside single/double-quoted strings.
     *
     * @param string $sql SQL with placeholders.
     * @param array $params Parameters list.
     * @return string Expanded SQL for display.
     */
    public static function expand_sql(string $sql, array $params): string {
        if (empty($params) || strpos($sql, "?") === false) {
            return $sql;
        }

        $out = "";
        $p = 0;

        $insingle = false;
        $indouble = false;

        $len = strlen($sql);
        for ($i = 0; $i < $len; $i++) {
            $ch = $sql[$i];

            if ($ch == "'" && !$indouble) {
                // Handle escaped single quote '' inside strings.
                if ($insingle && $i + 1 < $len && $sql[$i + 1] == "'") {
                    $out .= "''";
                    $i++;
                    continue;
                }
                $insingle = !$insingle;
                $out .= $ch;
                continue;
            }

            if ($ch === '"' && !$insingle) {
                $indouble = !$indouble;
                $out .= $ch;
                continue;
            }

            if ($ch == "?" && !$insingle && !$indouble) {
                if (array_key_exists($p, $params)) {
                    $out .= self::format_param_for_sql($params[$p]);
                } else {
                    $out .= "?";
                }
                $p++;
                continue;
            }

            $out .= $ch;
        }

        return $out;
    }

    /**
     * Formats params for a human-friendly display block.
     *
     * @param array $params Params list.
     * @return string Pretty params (one per line).
     */
    public static function format_params_block(array $params): string {
        if (empty($params)) {
            return "-";
        }

        $lines = [];
        foreach ($params as $i => $v) {
            $lines[] = "[{$i}] = " . self::format_param_for_sql($v);
        }
        return implode("\n", $lines);
    }

    /**
     * Parses a scalar from a var_export-like string.
     *
     * @param string $raw Raw value.
     * @return mixed Parsed scalar.
     */
    private static function parse_scalar(string $raw) {
        $raw = trim($raw);

        if (strcasecmp($raw, "NULL") == 0) {
            return null;
        }

        if (strcasecmp($raw, "true") == 0) {
            return true;
        }

        if (strcasecmp($raw, "false") == 0) {
            return false;
        }

        // Quoted string.
        if ((strpos($raw, "'") == 0 && substr($raw, -1) == "'") ||
            (strpos($raw, '"') == 0 && substr($raw, -1) == '"')) {
            $q = $raw[0];
            $inner = substr($raw, 1, -1);

            $inner = str_replace("\\$q", $q, $inner);
            $inner = str_replace("\\\\", "\\", $inner);

            return $inner;
        }

        // Numeric.
        if (is_numeric($raw)) {
            if (strpos($raw, ".") !== false) {
                return  $raw;
            }
            return $raw;
        }

        return $raw;
    }

    /**
     * Formats a scalar for safe SQL display (not for execution).
     *
     * @param mixed $value Any value.
     * @return string SQL literal string.
     */
    private static function format_param_for_sql($value): string {
        if ($value === null) {
            return "NULL";
        }

        if (is_bool($value)) {
            return $value ? "1" : "0";
        }

        if (is_int($value) || is_float($value)) {
            return $value;
        }

        $s = $value;
        $s = str_replace("'", "''", $s);
        return "'{$s}'";
    }
}
