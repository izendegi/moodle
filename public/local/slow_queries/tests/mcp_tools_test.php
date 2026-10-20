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
 * PHPUnit regression coverage for optional slow query MCP tools.
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_slow_queries;

use advanced_testcase;
use local_slow_queries\mcp\analyse_query;
use local_slow_queries\mcp\comment_service;
use local_slow_queries\mcp\get_query;
use local_slow_queries\mcp\list_queries;
use local_slow_queries\mcp\query_filter;
use local_slow_queries\mcp\save_comment;
use moodle_exception;

/**
 * Check bounds, administrator isolation and preview-only writes.
 *
 * @covers \local_slow_queries\mcp\query_filter
 * @covers \local_slow_queries\mcp\report_service
 * @covers \local_slow_queries\mcp\comment_service
 * @covers \local_slow_queries\mcp\get_query
 * @covers \local_slow_queries\mcp\list_queries
 * @covers \local_slow_queries\mcp\analyse_query
 * @covers \local_slow_queries\mcp\save_comment
 */
final class mcp_tools_test extends advanced_testcase {
    /**
     * Skip MCP integration tests if the optional dependency is absent.
     *
     * @return void
     */
    private function require_mcp(): void {
        if (!class_exists(\local_mcp\security\authenticated_identity::class)) {
            $this->markTestSkipped("Optional local_mcp is not installed.");
        }
    }

    /**
     * Reject unbounded filters instead of trusting the caller's schema.
     *
     * @return void
     */
    public function test_filter_defaults_and_bounds(): void {
        $this->resetAfterTest();
        $this->assertSame(["", 3.0, 0, 20, false], query_filter::parse([]));
        $this->assertSame(["SELECT", 5.0, 10, 5, true], query_filter::parse([
            "search" => " SELECT ", "min_seconds" => 5, "offset" => 10,
            "limit" => 5, "include_sql" => true,
        ]));
        $this->expectException(moodle_exception::class);
        query_filter::parse(["limit" => 1000]);
    }

    /**
     * The same query fingerprint can be annotated, but a stale note cannot overwrite it.
     *
     * @return void
     */
    public function test_comment_service_rejects_stale_notes(): void {
        global $DB;
        $this->resetAfterTest();
        $sql = "SELECT id FROM {course} WHERE id = 123456";
        $original = comment_service::state_hash($sql);
        $result = comment_service::save($sql, "Index review pending", $original);
        $this->assertSame(1, $DB->count_records("local_slow_queries_comments"));
        $this->assertSame("Index review pending", comment_service::find($sql)->comments);
        $this->assertNotSame($original, $result["state_hash"]);
        $this->expectException(moodle_exception::class);
        comment_service::save($sql, "Accidental stale overwrite", $original);
    }

    /**
     * Queries with identical long prefixes must not share investigation notes.
     *
     * @return void
     */
    public function test_comment_matching_distinguishes_long_shared_prefixes(): void {
        $this->resetAfterTest();
        $prefix = "SELECT " . str_repeat("a", 260);
        $firstsql = $prefix . " FROM {course}";
        $secondsql = $prefix . " FROM {user}";
        $first = comment_service::save($firstsql, "Course investigation",
            comment_service::state_hash($firstsql));
        $this->assertNull(comment_service::find($secondsql));
        $second = comment_service::save($secondsql, "User investigation",
            comment_service::state_hash($secondsql));
        $this->assertNotSame($first["comment_id"], $second["comment_id"]);
        $this->assertSame("Course investigation", comment_service::find($firstsql)->comments);
        $this->assertSame("User investigation", comment_service::find($secondsql)->comments);
    }

    /**
     * Notes are capped and cannot store arbitrary HTML.
     *
     * @return void
     */
    public function test_comment_validation(): void {
        $this->resetAfterTest();
        $this->assertSame("Review index", comment_service::validated_text("Review index"));
        $this->expectException(moodle_exception::class);
        comment_service::validated_text(str_repeat("x", comment_service::MAX_COMMENT_CHARS + 1));
    }

    /**
     * A connected student must not read SQL logs even when the PHP global user is admin.
     *
     * @return void
     */
    public function test_denies_nonadmin_mcp_identity(): void {
        $this->resetAfterTest();
        $this->require_mcp();
        $this->setAdminUser();
        $student = $this->getDataGenerator()->create_user();
        $identity = new \local_mcp\security\authenticated_identity(
            (int)$student->id, "oauth", ["read"]
        );
        $this->expectException(\required_capability_exception::class);
        (new list_queries())->execute([], $identity);
    }

    /**
     * Create a test-only log table if the Moodle test site has no slow logging enabled.
     *
     * @return array{bool, \xmldb_table}
     */
    private function ensure_query_table(): array {
        global $DB;
        $manager = $DB->get_manager();
        $table = new \xmldb_table("log_queries");
        if ($manager->table_exists($table)) {
            return [false, $table];
        }
        $table->add_field("id", XMLDB_TYPE_INTEGER, "10", null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field("qtype", XMLDB_TYPE_CHAR, "60", null, null, null);
        $table->add_field("sqltext", XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null);
        $table->add_field("sqlparams", XMLDB_TYPE_TEXT, null, null, null, null);
        $table->add_field("error", XMLDB_TYPE_INTEGER, "10", null, null, null);
        $table->add_field("info", XMLDB_TYPE_TEXT, null, null, null, null);
        $table->add_field("backtrace", XMLDB_TYPE_TEXT, null, null, null, null);
        $table->add_field("exectime", XMLDB_TYPE_NUMBER, "12,4", null, null, null);
        $table->add_field("timelogged", XMLDB_TYPE_INTEGER, "10", null, null, null);
        $table->add_key("primary", XMLDB_KEY_PRIMARY, ["id"]);
        $manager->create_table($table);
        return [true, $table];
    }

    /**
     * Sensitive fields remain opt-in; previews and READ never execute logged SQL.
     *
     * @return void
     */
    public function test_read_projections_and_write_preview(): void {
        global $DB, $USER;
        $this->resetAfterTest();
        $this->require_mcp();
        $this->setAdminUser();
        [$temporary, $table] = $this->ensure_query_table();
        try {
            $sql = "SELECT id FROM {course} WHERE id = ?";
            $id = $DB->insert_record("log_queries", (object)[
                "qtype" => "SELECT",
                "sqltext" => $sql,
                "sqlparams" => "[123]",
                "error" => 0,
                "info" => "test",
                "backtrace" => "lib/dml/moodle_database.php",
                "exectime" => 7.5,
                "timelogged" => time(),
            ]);
            $identity = new \local_mcp\security\authenticated_identity(
                (int)$USER->id, "oauth", ["read", "write"]
            );
            $list = (new list_queries())->execute(["min_seconds" => 3], $identity);
            $this->assertGreaterThanOrEqual(1, $list["total_groups"]);
            $this->assertArrayNotHasKey("sql_preview", $list["groups"][0]);
            $query = (new get_query())->execute(["query_id" => $id], $identity);
            $this->assertFalse($query["raw_sql_included"]);
            $this->assertFalse($query["sensitive_fields_included"]);
            $this->assertArrayNotHasKey("sql", $query);
            $this->assertArrayNotHasKey("sqlparams", $query);
            $full = (new get_query())->execute([
                "query_id" => $id, "include_sql" => true, "include_sensitive" => true,
            ], $identity);
            $this->assertSame($sql, $full["sql"]);
            $this->assertSame("[123]", $full["sqlparams"]);
            $analysis = (new analyse_query())->execute(["query_id" => $id], $identity);
            $this->assertFalse($analysis["ran_logged_sql"]);
            $this->assertFalse($analysis["created_indexes"]);
            $commentsbefore = $DB->count_records("local_slow_queries_comments");
            $preview = (new save_comment())->preview([
                "query_id" => $id,
                "comment" => "Review query performance",
                "expected_state_hash" => $query["comment_state_hash"],
            ], $identity);
            $this->assertTrue($preview["affects_all_identical_sql_queries"]);
            $this->assertSame($commentsbefore, $DB->count_records("local_slow_queries_comments"));
        } finally {
            if ($temporary) {
                $DB->get_manager()->drop_table($table);
            }
        }
    }
}
