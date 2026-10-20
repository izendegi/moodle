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
 * Save an administrator's confirmed note for a logged SQL fingerprint.
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_slow_queries\mcp;

use local_mcp\security\authenticated_identity;

/**
 * Preview exposes the current note; execution only changes plugin-owned comments.
 */
final class save_comment extends write_base {
    /**
     * Return the unique MCP tool identifier.
     *
     * @return string
     */
    public function get_name(): string {
        return "slowqueries_save_comment";
    }

    /**
     * Return the translated MCP tool title.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string("mcpsavecomment", "local_slow_queries");
    }

    /**
     * Return the translated MCP tool description.
     *
     * @return string
     */
    public function get_description(): string {
        return get_string("mcpsavecommentdesc", "local_slow_queries");
    }

    /**
     * Define the accepted MCP tool arguments.
     *
     * @return array
     */
    public function get_input_schema(): array {
        return $this->object_schema([
            "query_id" => ["type" => "integer", "minimum" => 1],
            "comment" => ["type" => "string", "maxLength" => comment_service::MAX_COMMENT_CHARS],
            "expected_state_hash" => ["type" => "string", "pattern" => "^[a-f0-9]{64}$"],
        ], ["query_id", "comment", "expected_state_hash"]);
    }

    /**
     * Validate all inputs without modifying the shared investigation note.
     *
     * @param array $arguments Arguments.
     * @param authenticated_identity $identity Administrator.
     * @return array
     */
    public function preview(array $arguments, authenticated_identity $identity): array {
        access::require_admin($identity);
        $row = report_service::query((int)$arguments["query_id"]);
        $sql = (string)$row->sqltext;
        comment_service::require_state($sql, (string)$arguments["expected_state_hash"]);
        $note = comment_service::find($sql);
        $new = comment_service::validated_text((string)$arguments["comment"]);
        return [
            "operation" => $this->get_name(),
            "query_id" => (int)$row->id,
            "fingerprint_sha256" => hash("sha256", $sql),
            "comment_before_preview" => \core_text::substr((string)($note->comments ?? ""), 0, 500),
            "new_comment" => \core_text::substr($new, 0, 500),
            "new_comment_chars" => \core_text::strlen($new),
            "affects_all_identical_sql_queries" => true,
            "changes_query_log" => false,
            "executes_sql_log_entry" => false,
        ];
    }

    /**
     * Execute an authorized tool operation.
     *
     * @param array $arguments Arguments.
     * @param authenticated_identity $identity Administrator.
     * @return array
     */
    public function execute(array $arguments, authenticated_identity $identity): array {
        access::require_admin($identity);
        $row = report_service::query((int)$arguments["query_id"]);
        return [
            "query_id" => (int)$row->id,
            "fingerprint_sha256" => hash("sha256", (string)$row->sqltext),
        ] + comment_service::save((string)$row->sqltext, (string)$arguments["comment"],
            (string)$arguments["expected_state_hash"]);
    }
}
