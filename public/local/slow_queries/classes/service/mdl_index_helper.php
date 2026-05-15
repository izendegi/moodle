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
 * Simple helper to secure indexes via XMLDB.
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_slow_queries\service;

use coding_exception;
use core\output\notification;
use ddl_exception;
use xmldb_index;
use xmldb_table;

/**
 * Simple helper to secure indexes via XMLDB.
 */
class mdl_index_helper {
    /** @var string */
    public static string $message;

    /**
     * Prints a short reference to a Moodle Tracker issue during upgrades.
     *
     * @param string $issuekey Issue key, e.g. "MDL-87790".
     * @param string $context Optional context prefix to explain why the issue is referenced.
     */
    public static function mtrace_tracker_issue(string $issuekey, string $context): void {
        $url = "https://moodle.atlassian.net/browse/{$issuekey}";
        self::$message = "{$context} See {$issuekey} for details: <a href='{$url}' target='_blank'>{$issuekey}</a>";
    }

    /**
     * It ensures that an index exists, and if it doesn't, it creates one.
     *
     * @param string $tablename Table name WITHOUT prefix (e.g., 'page')
     * @param string[] $columns Index columns
     * @throws ddl_exception
     * @throws coding_exception
     */
    public static function ensure_index(string $tablename, array $columns): void {
        global $DB, $OUTPUT;

        $table = new xmldb_table($tablename);
        $index = new xmldb_index(self::make_name($columns), XMLDB_INDEX_NOTUNIQUE, $columns);

        $dbman = $DB->get_manager();
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
            $notification = new notification(self::$message, notification::NOTIFY_INFO);
            $notification->set_show_closebutton();
            echo $OUTPUT->render($notification);
        }
    }

    /**
     * Build an index name from columns, respecting max name length per DB family.
     *
     * Rule: start with "col1_col2_col3". If too long, remove ONE character from the end of
     * EVERY column (that still has > 1 char) per iteration, until it fits.
     *
     * Notes:
     * - Limits are in BYTES (not Unicode chars).
     * - If we cannot shrink enough (edge case), we fall back to a truncated name + hash.
     *
     * @param string[] $columns
     * @return string
     * @throws coding_exception
     */
    private static function make_name(array $columns): string {
        global $DB;

        switch ($DB->get_dbfamily()) {
            case "postgres":
                $maxbytes = 63;
                break;
            case "mysql":
                $maxbytes = 64;
                break;
            case "mariadb":
                $maxbytes = 64;
                break;
            case "oracle":
                $maxbytes = 30;
                break;
            case "mssql":
                throw new coding_exception(
                    "Do not use Moodle on Windows/MSSQL. Use Linux with PostgreSQL/MySQL/MariaDB (or Oracle)."
                );
            default:
                $maxbytes = 30;
        }

        $origcolumns = array_values($columns);
        $work = $origcolumns;

        $name = implode("_", $work);
        while (strlen($name) > $maxbytes) {
            $changed = false;

            // Remove ONE character from EACH column (per iteration) until the name fits.
            foreach ($work as $i => $col) {
                if (strlen($col) > 1) {
                    $work[$i] = substr($col, 0, -1);
                    $changed = true;
                }
            }

            $name = implode("_", $work);

            // Safety net: if nothing can be shortened anymore, stop shrinking.
            if (!$changed) {
                break;
            }
        }

        // Edge-case fallback: still too long (e.g., many columns and all are 1 char already).
        if (strlen($name) > $maxbytes) {
            $hash = substr(sha1(implode("_", $origcolumns)), 0, 6);
            $keep = max(0, $maxbytes - 7); // Start "_" + 6 hash chars.
            $name = substr($name, 0, $keep) . "_{$hash}";
        }

        return $name;
    }
}
