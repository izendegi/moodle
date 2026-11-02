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
 * Persist confirmed investigation notes for existing SQL log entries.
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_slow_queries\mcp;

use core\lock\lock_config;
use moodle_exception;
use stdClass;

/**
 * Preserve comments shared by identical SQL statements.
 */
final class comment_service {
    /** Maximum characters in an administrator's confirmed note. */
    public const MAX_COMMENT_CHARS = 5000;

    /**
     * Load a comment row, rejecting ambiguous duplicates rather than updating the wrong row.
     *
     * @param string $sqltext Identical SQL fingerprint source.
     * @return stdClass|null
     */
    public static function find(string $sqltext): ?stdClass {
        global $DB;
        // Moodle's record APIs do not permit direct equality conditions on TEXT fields.
        // Compare a portable prefix in SQL, then check complete text in PHP to avoid
        // false matches from shared prefixes or case-insensitive database collations.
        $where = $DB->sql_compare_text("sqltext", 255) . " = " .
            $DB->sql_compare_text(":sqltext", 255);
        $recordset = $DB->get_recordset_select("local_slow_queries_comments", $where,
            ["sqltext" => $sqltext],
            "id ASC", "id,sqltext,comments,timemodified");
        $match = null;
        try {
            foreach ($recordset as $row) {
                if ((string)$row->sqltext !== $sqltext) {
                    continue;
                }
                if ($match !== null) {
                    throw new moodle_exception("mcpduplicatecomments", "local_slow_queries");
                }
                $match = $row;
            }
        } finally {
            $recordset->close();
        }
        return $match;
    }

    /**
     * A content-based hash avoids relying solely on second-resolution timestamps.
     *
     * @param string $sqltext Exact logged SQL.
     * @return string
     */
    public static function state_hash(string $sqltext): string {
        $note = self::find($sqltext);
        return hash("sha256", json_encode([
            $sqltext,
            $note ? [(int)$note->id, (string)$note->comments, (int)$note->timemodified] : null,
        ], JSON_THROW_ON_ERROR));
    }

    /**
     * Reject a stale expected comment state.
     *
     * @param string $sqltext Exact logged SQL.
     * @param string $expected Caller-provided current comment hash.
     * @return void
     */
    public static function require_state(string $sqltext, string $expected): void {
        if (!preg_match("/^[0-9a-f]{64}$/", $expected) ||
                !hash_equals(self::state_hash($sqltext), $expected)) {
            throw new moodle_exception("mcpstalecomment", "local_slow_queries");
        }
    }

    /**
     * Validate and sanitize a proposed investigation note.
     *
     * @param string $text User-supplied note.
     * @return string
     */
    public static function validated_text(string $text): string {
        if (strlen($text) > self::MAX_COMMENT_CHARS) {
            throw new moodle_exception("mcpinvalidcomment", "local_slow_queries");
        }
        return clean_param($text, PARAM_TEXT);
    }

    /**
     * Serialize writes by SQL fingerprint and reject stale confirmations.
     *
     * @param string $sqltext Actual logged SQL text.
     * @param string $text Confirmed note.
     * @param string $expected State hash shown before confirmation.
     * @return array
     */
    public static function save(string $sqltext, string $text, string $expected): array {
        global $DB;
        $text = self::validated_text($text);
        $lock = lock_config::get_lock_factory("local_slow_queries")->get_lock(
            "mcp_comment_" . hash("sha256", $sqltext), 10
        );
        if (!$lock) {
            throw new moodle_exception("mcpcommentbusy", "local_slow_queries");
        }
        try {
            self::require_state($sqltext, $expected);
            $record = self::find($sqltext);
            if ($record) {
                $DB->update_record("local_slow_queries_comments", (object)[
                    "id" => (int)$record->id,
                    "comments" => $text,
                    "timemodified" => time(),
                ]);
                $id = (int)$record->id;
            } else {
                $id = (int)$DB->insert_record("local_slow_queries_comments", (object)[
                    "sqltext" => $sqltext,
                    "comments" => $text,
                    "timemodified" => time(),
                ]);
            }
            return [
                "comment_id" => $id,
                "comment_length" => strlen($text),
                "state_hash" => self::state_hash($sqltext),
            ];
        } finally {
            $lock->release();
        }
    }
}
