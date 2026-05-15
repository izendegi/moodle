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
 * Generates EXPLAIN output for MySQL and returns it as Markdown.
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_slow_queries\service;

/**
 * Generates EXPLAIN output for MySQL and returns it as Markdown.
 */
class explain_service {
    /**
     * Runs EXPLAIN for a SQL statement (MySQL/MariaDB only) and returns a Markdown table.
     *
     * @param string $sql SQL statement (typically SELECT) with Moodle placeholders (?).
     * @param array $params Placeholder parameters in order.
     */
    public static function explain_to_markdown(string $sql): string {
        global $DB;

        if ($DB->get_dbfamily() !== "mysql") {
            return "";
        }

        $sql = trim($sql);
        $sql = rtrim($sql, ";");
        if ($sql === "") {
            return "";
        }

        $explainsql = "EXPLAIN {$sql}";

        try {
            $rs = $DB->get_recordset_sql($explainsql);
        } catch (\Throwable $e) {
            return "";
        }

        $rows = [];
        $columns = [];

        try {
            foreach ($rs as $row) {
                $arr = (array) $row;

                if (empty($columns)) {
                    $columns = array_keys($arr);
                }

                $rows[] = $arr;
            }
        } finally {
            $rs->close();
        }

        if (empty($rows)) {
            return "";
        }

        return self::build_markdown_table($columns, $rows);
    }

    /**
     * Builds a Markdown table from rows.
     *
     * @param array $columns Column names in order.
     * @param array $rows Rows as associative arrays.
     * @return string Markdown table.
     */
    private static function build_markdown_table(array $columns, array $rows): string {
        // Header.
        $out[] = "| " . implode(" | ", array_map([self::class, "md_escape"], $columns)) . " |";

        foreach ($rows as $row) {
            $cells = [];
            foreach ($columns as $col) {
                $val = array_key_exists($col, $row) ? $row[$col] : null;

                if ($val === null) {
                    $cells[] = "";
                } else if (is_bool($val)) {
                    $cells[] = $val ? "1" : "0";
                } else {
                    $cells[] = self::md_escape((string) $val);
                }
            }

            $out[] = "| " . implode(" | ", $cells) . " |";
        }

        return implode("\n", $out);
    }

    /**
     * Escapes values to be safe inside a Markdown table cell.
     *
     * @param string $value Raw value.
     * @return string Escaped value.
     */
    private static function md_escape(string $value): string {
        $value = str_replace("\r\n", "\n", $value);
        $value = str_replace("\r", "\n", $value);
        $value = str_replace("|", "\\|", $value);
        $value = str_replace("\n", "<br>", $value);
        $value = trim($value);
        return $value;
    }

    /**
     * Returns a simple Markdown info message.
     *
     * @param string $msg Message.
     * @return string Markdown.
     */
    private static function md_info(string $msg): string {
        return "_Info:_ {$msg}";
    }

    /**
     * Returns a simple Markdown error message.
     *
     * @param string $msg Message.
     * @return string Markdown.
     */
    private static function md_error(string $msg): string {
        return "**Error:** {$msg}";
    }
}
