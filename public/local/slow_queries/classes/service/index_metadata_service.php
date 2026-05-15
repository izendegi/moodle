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
 * index_metadata_service.php
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_slow_queries\service;

use dml_exception;

/**
 * Reads existing index metadata from the current DB engine.
 *
 * This service is read-only and intended for diagnostics/prompts only.
 */
class index_metadata_service {
    /**
     * Gets existing indexes for a Moodle table (without prefix).
     *
     * @param string $prefix Moodle DB prefix (e.g. "mdl_").
     * @param string $moodletable Moodle table name without prefix (e.g. "course").
     * @return array List of indexes: [ ["name" => string, "unique" => bool, "columns" => string[], "definition" => ?string], ... ]
     * @throws dml_exception
     */
    public static function get_indexes(string $prefix, string $moodletable): array {
        global $DB;

        $family = method_exists($DB, "get_dbfamily") ? $DB->get_dbfamily() : "";

        if ($family === "mysql" || $family === "mariadb") {
            return self::get_mysql_indexes($prefix, $moodletable);
        }

        if ($family === "postgres") {
            return self::get_postgres_indexes($prefix, $moodletable);
        }

        return [];
    }

    /**
     * Formats index info as a readable block for prompts.
     *
     * @param array $indexes Index list from get_indexes().
     * @return string Formatted text.
     */
    public static function format_indexes_block(array $indexes): string {
        if (empty($indexes)) {
            return "- (unavailable or none found via metadata)";
        }

        $lines = [];
        foreach ($indexes as $idx) {
            $name = ($idx["name"] ?? "");
            $unique = !empty($idx["unique"]);
            $cols = (array) ($idx["columns"] ?? []);
            $def = $idx["definition"] ?? "";

            $tag = $unique ? "UNIQUE" : "INDEX";

            if (!empty($cols)) {
                $lines[] = "- {$name} ({$tag}): " . implode(", ", $cols);
                continue;
            }

            if ($def !== "") {
                $lines[] = "- {$name} ({$tag}): {$def}";
                continue;
            }

            $lines[] = "- {$name} ({$tag})";
        }

        return implode("\n", $lines);
    }

    /**
     * Gets indexes for MySQL/MariaDB using information_schema.
     *
     * Important: get_records_sql() would overwrite rows because the first selected column repeats (index_name).
     * We use a recordset to preserve all rows (multi-column indexes).
     *
     * @param string $prefix Prefix.
     * @param string $moodletable Table without prefix.
     * @return array Index list.
     * @throws dml_exception
     */
    private static function get_mysql_indexes(string $prefix, string $moodletable): array {
        global $DB;

        $real = $prefix . $moodletable;

        $schema = $DB->get_field_sql("SELECT DATABASE()");
        if (!$schema) {
            return [];
        }

        $sql = "
            SELECT index_name, non_unique, seq_in_index, column_name
              FROM information_schema.statistics
             WHERE table_schema = :schema
               AND table_name = :tname
          ORDER BY index_name ASC, seq_in_index ASC";

        $rs = $DB->get_recordset_sql($sql, ["schema" => $schema, "tname" => $real]);

        $byname = [];
        foreach ($rs as $r) {
            $name = $r->index_name;
            $col = strtolower($r->column_name);
            $unique = ($r->non_unique === 0);

            if (!isset($byname[$name])) {
                $byname[$name] = [
                    "name" => $name,
                    "unique" => $unique,
                    "columns" => [],
                    "definition" => null,
                ];
            }

            // If any row says unique, keep it as unique.
            $byname[$name]["unique"] = $byname[$name]["unique"] || $unique;
            $byname[$name]["columns"][] = $col;
        }
        $rs->close();

        return array_values($byname);
    }

    /**
     * Gets indexes for PostgreSQL using pg_indexes.
     *
     * Important: get_records_sql() would overwrite rows because the first selected column repeats (schemaname).
     * We use a recordset to preserve all rows.
     *
     * @param string $prefix Prefix.
     * @param string $moodletable Table without prefix.
     * @return array Index list.
     * @throws dml_exception
     */
    private static function get_postgres_indexes(string $prefix, string $moodletable): array {
        global $DB;

        $real = $prefix . $moodletable;

        $sql = "
            SELECT schemaname, tablename, indexname, indexdef
              FROM pg_indexes
             WHERE tablename = :tname";

        $rs = $DB->get_recordset_sql($sql, ["tname" => $real]);

        $out = [];
        foreach ($rs as $r) {
            $name = $r->indexname;
            $def = $r->indexdef;

            $unique = (stripos($def, "CREATE UNIQUE INDEX") !== false);

            $cols = [];
            if (preg_match('/\((.+)\)/', $def, $m)) {
                $colsraw = $m[1];
                $parts = preg_split("/,/", $colsraw);

                foreach ($parts as $p) {
                    $p = trim($p);
                    $p = preg_replace('/\s+(ASC|DESC)\b/i', "", $p);
                    $p = trim($p, "\"` ");

                    // Keep only plain column names, skip expressions (best-effort).
                    if ($p === "" || strpos($p, "(") !== false) {
                        continue;
                    }

                    $cols[] = strtolower($p);
                }
            }

            $out[] = [
                "name" => $name,
                "unique" => $unique,
                "columns" => $cols,
                "definition" => $def,
            ];
        }
        $rs->close();

        return $out;
    }
}
