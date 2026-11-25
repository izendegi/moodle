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
 * Regression tests for slow-query database index installation.
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_slow_queries;

use advanced_testcase;
use local_slow_queries\service\mdl_index_helper;
use xmldb_table;

/**
 * Verify indexes are only created after their owning plugin tables exist.
 *
 * @covers \local_slow_queries\service\mdl_index_helper
 */
final class index_helper_test extends advanced_testcase {
    /**
     * A missing core plugin table must not prevent first-time Moodle installation.
     *
     * @return void
     */
    public function test_missing_core_plugin_table_is_skipped(): void {
        global $DB;
        $this->resetAfterTest();

        $table = new xmldb_table("slowqueries_absent");
        $this->assertFalse($DB->get_manager()->table_exists($table));

        // The logstore plugin table does not exist yet during the local plugin install hook.
        mdl_index_helper::ensure_index("slowqueries_absent", ["userid", "timemodified"]);

        $this->assertFalse($DB->get_manager()->table_exists($table));
    }
}
