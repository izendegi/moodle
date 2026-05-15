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
 * index_suggestion_service.php
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_slow_queries\service;

use dml_exception;

/**
 * Heuristic index suggestion engine for slow SQL logs.
 *
 * This does NOT execute any DDL. It only suggests potential indexes to test.
 */
class index_suggestion_service {
    /**
     * Suggests possible missing indexes based on JOIN/WHERE/ORDER/GROUP clauses.
     *
     * @param string $prefix Moodle table prefix.
     * @param string $sql SQL text.
     * @return array Suggestions list.
     * @throws dml_exception
     */
    public static function suggest(string $prefix, string $sql): array {
        $sql = trim($sql);
        if ($sql === "") {
            return [];
        }

        $aliasmap = self::extract_alias_map($sql);
        $features = self::extract_features($sql, $aliasmap);

        if (empty($features)) {
            return [];
        }

        $existingcache = [];
        $suggestions = [];

        foreach ($features as $moodletable => $info) {
            if (empty($info["candidates"])) {
                continue;
            }

            if (!array_key_exists($moodletable, $existingcache)) {
                $existingcache[$moodletable] = self::get_existing_indexes($prefix, $moodletable);
            }
            $existing = $existingcache[$moodletable];

            foreach ($info["candidates"] as $candidate) {
                $cols = $candidate["columns"];
                if (empty($cols)) {
                    continue;
                }

                if (self::is_covered_by_existing($existing, $cols)) {
                    continue;
                }

                $suggestions[] = [
                    "table" => $moodletable,
                    "columns" => $cols,
                    "reason" => $candidate["reason"],
                    "create" => self::build_create_index_sql($prefix, $moodletable, $cols),
                ];
            }
        }

        // Remove duplicates.
        $uniq = [];
        foreach ($suggestions as $s) {
            $key = "{$s["table"]}:" . implode(",", $s["columns"]);
            $uniq[$key] = $s;
        }

        return array_values($uniq);
    }

    /**
     * Extracts table aliases from FROM/JOIN clauses.
     *
     * @param string $sql SQL text.
     * @return array alias => rawtable
     */
    private static function extract_alias_map(string $sql): array {
        $map = [];

        $pattern = '/\b(FROM|JOIN)\s+([`"]?)(\{?[a-z0-9_]+\}?)(\2)\s+(?:AS\s+)?([a-z0-9_]+)/i';
        if (preg_match_all($pattern, $sql, $m)) {
            $count = count($m[0]);
            for ($i = 0; $i < $count; $i++) {
                $rawtable = $m[3][$i];
                $alias = $m[5][$i];
                $map[$alias] = $rawtable;
            }
        }

        return $map;
    }

    /**
     * Extracts per-table features and builds candidate index column sets.
     *
     * @param string $sql SQL text.
     * @param array $aliasmap alias => rawtable
     * @return array moodletable => features (eq/range/order/group/candidates)
     */
    private static function extract_features(string $sql, array $aliasmap): array {
        global $CFG;

        $prefix = $CFG->prefix ?? "mdl_";

        $bytable = [];

        $ensure = function(string $moodletable) use (&$bytable): void {
            if (!isset($bytable[$moodletable])) {
                $bytable[$moodletable] = [
                    "eq" => [],
                    "range" => [],
                    "order" => [],
                    "group" => [],
                    "candidates" => [],
                ];
            }
        };

        $addcol = function(string $moodletable, string $bucket, string $col) use (&$bytable, $ensure): void {
            $ensure($moodletable);

            $col = strtolower($col);
            if ($col === "id") {
                return; // Primary key is assumed.
            }

            if (!in_array($col, $bytable[$moodletable][$bucket], true)) {
                $bytable[$moodletable][$bucket][] = $col;
            }
        };

        $resolve = function(string $alias) use ($aliasmap, $prefix): ?string {
            if (!isset($aliasmap[$alias])) {
                return null;
            }
            $raw = $aliasmap[$alias];
            return self::normalize_table_name($raw, $prefix);
        };

        // 1) Equality joins anywhere: a.x = b.y (covers ON and WHERE).
        $eqpattern = '/\b([a-z0-9_]+)\.([a-z0-9_]+)\s*=\s*([a-z0-9_]+)\.([a-z0-9_]+)/i';
        if (preg_match_all($eqpattern, $sql, $m)) {
            $count = count($m[0]);
            for ($i = 0; $i < $count; $i++) {
                $a1 = $m[1][$i];
                $c1 = $m[2][$i];
                $a2 = $m[3][$i];
                $c2 = $m[4][$i];

                $t1 = $resolve($a1);
                $t2 = $resolve($a2);

                if ($t1) {
                    $addcol($t1, "eq", $c1);
                }
                if ($t2) {
                    $addcol($t2, "eq", $c2);
                }
            }
        }

        // 2) WHERE clause comparisons with placeholders: alias.col OP ?  / IN ( / LIKE ?
        $where = self::extract_clause($sql, "WHERE", ["GROUP BY", "ORDER BY", "LIMIT", "HAVING"]);
        if ($where !== "") {
            $cmp = '/\b([a-z0-9_]+)\.([a-z0-9_]+)\s*(=|>=|<=|<|>|IN|LIKE)\s*(\?|\()/i';
            if (preg_match_all($cmp, $where, $m)) {
                $count = count($m[0]);
                for ($i = 0; $i < $count; $i++) {
                    $alias = $m[1][$i];
                    $col = $m[2][$i];
                    $op = strtoupper($m[3][$i]);

                    $t = $resolve($alias);
                    if (!$t) {
                        continue;
                    }

                    if ($op === "=" || $op === "IN") {
                        $addcol($t, "eq", $col);
                    } else {
                        $addcol($t, "range", $col);
                    }
                }
            }
        }

        // 3) ORDER BY alias.col
        $order = self::extract_clause($sql, "ORDER BY", ["LIMIT"]);
        if ($order !== "") {
            $parts = preg_split("/,/", $order);
            foreach ($parts as $p) {
                $p = trim($p);
                if ($p === "") {
                    continue;
                }
                if (preg_match('/\b([a-z0-9_]+)\.([a-z0-9_]+)/i', $p, $mm)) {
                    $t = $resolve($mm[1]);
                    if ($t) {
                        $addcol($t, "order", $mm[2]);
                    }
                }
            }
        }

        // 4) GROUP BY alias.col
        $group = self::extract_clause($sql, "GROUP BY", ["ORDER BY", "LIMIT", "HAVING"]);
        if ($group !== "") {
            $parts = preg_split("/,/", $group);
            foreach ($parts as $p) {
                $p = trim($p);
                if ($p === "") {
                    continue;
                }
                if (preg_match('/\b([a-z0-9_]+)\.([a-z0-9_]+)/i', $p, $mm)) {
                    $t = $resolve($mm[1]);
                    if ($t) {
                        $addcol($t, "group", $mm[2]);
                    }
                }
            }
        }

        // Build candidates per table.
        foreach ($bytable as $t => $info) {
            $eq = $info["eq"];
            $range = $info["range"];
            $ordercols = $info["order"];

            // Candidate A: eq columns (and range if exists).
            $colsa = array_values(array_unique(array_merge($eq, $range)));
            $colsa = self::limit_cols($colsa);

            if (!empty($colsa)) {
                $bytable[$t]["candidates"][] = [
                    "columns" => $colsa,
                    "reason" => self::build_reason($eq, $range, $ordercols, "eq+range"),
                ];
            }

            // Candidate B: eq + order (helpful for ORDER BY after filtering).
            $colsb = array_values(array_unique(array_merge($eq, $ordercols)));
            $colsb = self::limit_cols($colsb);

            if (!empty($colsb) && $colsb !== $colsa) {
                $bytable[$t]["candidates"][] = [
                    "columns" => $colsb,
                    "reason" => self::build_reason($eq, [], $ordercols, "eq+order"),
                ];
            }

            // Candidate C: single-column indexes for join/filter keys (only eq set).
            foreach ($eq as $c) {
                $single = [$c];
                $bytable[$t]["candidates"][] = [
                    "columns" => $single,
                    "reason" => "Single-column equality/join key: {$c}",
                ];
            }

            // Deduplicate candidates inside table.
            $dedup = [];
            foreach ($bytable[$t]["candidates"] as $cand) {
                $key = implode(",", $cand["columns"]);
                $dedup[$key] = $cand;
            }
            $bytable[$t]["candidates"] = array_values($dedup);
        }

        return $bytable;
    }

    /**
     * Extracts a clause substring: START ... until any END keyword.
     *
     * @param string $sql SQL.
     * @param string $start Start keyword.
     * @param array $endkeywords Keywords that can end the clause.
     * @return string Clause content.
     */
    private static function extract_clause(string $sql, string $start, array $endkeywords): string {
        $up = strtoupper($sql);
        $spos = strpos($up, strtoupper($start));
        if ($spos === false) {
            return "";
        }

        $spos += strlen($start);
        $rest = substr($sql, $spos);

        $endpos = null;
        foreach ($endkeywords as $end) {
            $p = stripos($rest, $end);
            if ($p !== false) {
                if ($endpos === null || $p < $endpos) {
                    $endpos = $p;
                }
            }
        }

        if ($endpos !== null) {
            $rest = substr($rest, 0, $endpos);
        }

        return trim($rest);
    }

    /**
     * Normalizes raw table name: strips {}, quotes, and prefix.
     *
     * @param string $raw Raw table name from SQL.
     * @param string $prefix Moodle prefix.
     * @return string Moodle table name (without prefix).
     */
    private static function normalize_table_name(string $raw, string $prefix): string {
        $t = trim($raw);
        $t = trim($t, "{}");
        $t = trim($t, "`\"");

        if (strpos($t, $prefix) === 0) {
            return substr($t, strlen($prefix));
        }

        if (strpos($t, "mdl_") === 0 && $prefix !== "mdl_") {
            return substr($t, 4);
        }

        return $t;
    }

    /**
     * Returns existing indexes as list of column lists.
     *
     * @param string $prefix Prefix.
     * @param string $moodletable Table without prefix.
     * @return array List of indexes (each is array of column names in order).
     * @throws dml_exception
     */
    private static function get_existing_indexes(string $prefix, string $moodletable): array {
        global $DB;

        $family = method_exists($DB, "get_dbfamily") ? $DB->get_dbfamily() : "";

        if ($family === "mysql" || $family === "mariadb") {
            return self::get_mysql_indexes($prefix, $moodletable);
        }

        if ($family === "postgres") {
            return self::get_postgres_indexes($prefix, $moodletable);
        }

        // Unknown family: return empty (no "covered" checks).
        return [];
    }

    /**
     * Fetches MySQL/MariaDB indexes from information_schema.
     *
     * @param string $prefix Prefix.
     * @param string $moodletable Table without prefix.
     * @return array Index column lists.
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
            SELECT index_name, seq_in_index, column_name
            FROM information_schema.statistics
            WHERE table_schema = :schema
            AND table_name = :tname
            ORDER BY index_name ASC, seq_in_index ASC";

        $rs = $DB->get_recordset_sql($sql, ["schema" => $schema, "tname" => $real]);

        $idx = [];
        foreach ($rs as $r) {
            $name = $r->index_name;
            $col = strtolower($r->column_name);

            if (!isset($idx[$name])) {
                $idx[$name] = [];
            }
            $idx[$name][] = $col;
        }
        $rs->close();

        return array_values($idx);
    }

    /**
     * Fetches PostgreSQL indexes from pg_indexes.
     *
     * @param string $prefix Prefix.
     * @param string $moodletable Table without prefix.
     * @return array Index column lists.
     * @throws dml_exception
     */
    private static function get_postgres_indexes(string $prefix, string $moodletable): array {
        global $DB;

        $real = $prefix . $moodletable;

        $sql = "
            SELECT indexdef
            FROM pg_indexes
            WHERE tablename = :tname";

        $rs = $DB->get_recordset_sql($sql, ["tname" => $real]);

        $out = [];
        foreach ($rs as $r) {
            $def = $r->indexdef;

            if (!preg_match('/\((.+)\)/', $def, $m)) {
                continue;
            }

            $colsraw = $m[1];
            $parts = preg_split("/,/", $colsraw);

            $cols = [];
            foreach ($parts as $p) {
                $p = trim($p);
                $p = preg_replace('/\s+(ASC|DESC)\b/i', "", $p);
                $p = trim($p, "\"` ");
                if ($p === "" || strpos($p, "(") !== false) {
                    continue;
                }
                $cols[] = strtolower($p);
            }

            if (!empty($cols)) {
                $out[] = $cols;
            }
        }
        $rs->close();

        return $out;
    }

    /**
     * Checks if a candidate column list is covered by an existing index prefix.
     *
     * @param array $existing List of indexes.
     * @param array $candidate Candidate columns.
     * @return bool True when covered.
     */
    private static function is_covered_by_existing(array $existing, array $candidate): bool {
        if (empty($existing) || empty($candidate)) {
            return false;
        }

        $cand = array_map("strtolower", $candidate);

        foreach ($existing as $idxcols) {
            $idxcols = array_map("strtolower", (array) $idxcols);

            // Covered if candidate is a prefix of an existing index.
            $slice = array_slice($idxcols, 0, count($cand));
            if ($slice === $cand) {
                return true;
            }

            // Also consider single-column covered if column is first in any index.
            if (count($cand) === 1 && !empty($idxcols) && $idxcols[0] === $cand[0]) {
                return true;
            }
        }

        return false;
    }

    /**
     * Builds a CREATE INDEX suggestion SQL (display only).
     *
     * @param string $prefix Prefix.
     * @param string $table Moodle table name (no prefix).
     * @param array $cols Columns.
     * @return string SQL statement.
     */
    private static function build_create_index_sql(string $prefix, string $table, array $cols): string {
        $cols = array_map(function(string $c): string {
            return trim($c);
        }, $cols);

        $name = "idx_lsq_{$table}_" . implode("_", $cols);
        $name = preg_replace('/[^a-z0-9_]+/i', "_", $name);
        $name = substr(strtolower($name), 0, 60);

        $realtable = $prefix . $table;
        return "CREATE INDEX {$name} ON {$realtable} (" . implode(", ", $cols) . ");";
    }

    /**
     * Builds a human readable reason string.
     *
     * @param array $eq Equality columns.
     * @param array $range Range columns.
     * @param array $order Order columns.
     * @param string $kind Candidate kind.
     * @return string Reason.
     */
    private static function build_reason(array $eq, array $range, array $order, string $kind): string {
        $parts = [];
        if (!empty($eq)) {
            $parts[] = "Equality/join keys: " . implode(", ", $eq);
        }
        if (!empty($range)) {
            $parts[] = "Range keys: " . implode(", ", $range);
        }
        if (!empty($order)) {
            $parts[] = "Order keys: " . implode(", ", $order);
        }
        $parts[] = "Heuristic: " . $kind;

        return implode(" | ", $parts);
    }

    /**
     * Limits index columns to a safe size for suggestions.
     *
     * @param array $cols Columns.
     * @param int $max Max columns.
     * @return array Limited columns.
     */
    private static function limit_cols(array $cols, int $max = 4): array {
        $cols = array_values(array_filter($cols, function($c) {
            return is_string($c) && trim($c) !== "";
        }));

        if (count($cols) > $max) {
            $cols = array_slice($cols, 0, $max);
        }

        return $cols;
    }

    /**
     * Fallback used only during feature building (no DB call).
     *
     * @param string $table Table.
     * @return array Empty list.
     */
    private static function get_existing_indexes_fallback(string $table): array {
        return [];
    }
}
