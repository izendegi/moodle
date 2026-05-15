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
 * phpcs:disable moodle.Strings.ForbiddenStrings.Found
 * phpcs:disable Generic.CodeAnalysis.EmptyStatement.DetectedCatch
 * phpcs:disable Squiz.Commenting.EmptyCatchComment.Missing
 * table_schema_service.php
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_slow_queries\service;

use dml_exception;
use Throwable;

/**
 * Extracts table names from SQL and prints a compact schema from Moodle metadata.
 *
 * This includes table columns and (when possible) existing index metadata.
 */
class table_schema_service {
    /**
     * Extracts table names (best-effort) from SQL text.
     *
     * @param string $sql SQL text.
     * @return array Unique list of table names as they appear (may include prefix or {}).
     */
    public static function extract_tables(string $sql): array {
        $tables = [];

        $patterns = [
            '/\bFROM\s+([`"]?)(\{?[a-zA-Z0-9_]+\}?)(\1)/i',
            '/\bJOIN\s+([`"]?)(\{?[a-zA-Z0-9_]+\}?)(\1)/i',
            '/\bUPDATE\s+([`"]?)(\{?[a-zA-Z0-9_]+\}?)(\1)/i',
            '/\bINTO\s+([`"]?)(\{?[a-zA-Z0-9_]+\}?)(\1)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $sql, $m)) {
                foreach ($m[2] as $t) {
                    $tables[] = $t;
                }
            }
        }

        $tables = array_map(function(string $t): string {
            $t = trim($t);
            $t = trim($t, "{}");
            return $t;
        }, $tables);

        $tables = array_values(array_unique(array_filter($tables)));
        sort($tables);

        return $tables;
    }

    /**
     * Produces a readable schema block for the extracted tables.
     * Includes:
     * - columns (from Moodle metadata)
     * - existing indexes (best-effort, DB-family dependent)
     *
     * @param string $prefix DB prefix.
     * @param array $tables Table names from extract_tables().
     * @return string A schema+indexes block.
     * @throws dml_exception
     */
    public static function build_schema_block(string $prefix, array $tables): string {
        global $DB;

        if (empty($tables)) {
            return "No tables detected from SQL text.";
        }

        $out = [];
        foreach ($tables as $rawname) {
            $moodlename = self::normalize_to_moodle_table($rawname, $prefix);

            // Columns.
            try {
                $cols = $DB->get_columns($moodlename);
            } catch (Throwable) {
                continue;
            }

            $out[] = "## TABLE {$rawname}";

            try {
                $countrows = $DB->count_records_select($moodlename, "");
                $countrows = number_format($countrows);
                $out[] = "Row count: {$countrows}";
            } catch (Throwable) {
            }

            $out[] = "### Columns:";
            foreach ($cols as $c) {
                $type = $c->type ?? "";
                $len = isset($c->max_length) && $c->max_length ? "({$c->max_length})" : "";
                $nn = !empty($c->not_null) ? " NOT NULL" : "";
                $def = property_exists($c, "default_value") && $c->default_value !== null ? " DEFAULT {$c->default_value}" : "";
                $out[] = "  - {$c->name}: {$type}{$len}{$nn}{$def}";
            }

            // Existing indexes (best-effort).
            $out[] = "### Indexes:";
            $indexes = index_metadata_service::get_indexes($prefix, $moodlename);
            $idxblock = index_metadata_service::format_indexes_block($indexes);
            foreach (preg_split("/\r\n|\n|\r/", $idxblock) as $line) {
                $out[] = "  {$line}";
            }

            $out[] = "";
        }

        return trim(implode("\n", $out));
    }

    /**
     * Converts a raw SQL table name to Moodle table name (without prefix).
     *
     * @param string $rawname Table name from SQL.
     * @param string $prefix Moodle prefix.
     * @return string Moodle table name.
     */
    private static function normalize_to_moodle_table(string $rawname, string $prefix): string {
        $t = trim($rawname);
        $t = trim($t, "{}");
        $t = trim($t, "`\"");

        if (strpos($t, $prefix) === 0) {
            return substr($t, strlen($prefix));
        }
        if (strpos($t, "mdl_") === 0 && $prefix !== "mdl_") {
            // In case logs stored mdl_ explicitly but the site uses another prefix.
            return substr($t, 4);
        }
        return $t;
    }
}
