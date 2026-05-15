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
 * Upgrades plugin database schema and data.
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_slow_queries\service\mdl_index_helper;

/**
 * Upgrades plugin database schema and data.
 *
 * @param int $oldversion The old plugin version.
 * @return bool True on success.
 *
 * @throws coding_exception
 * @throws ddl_exception
 */
function xmldb_local_slow_queries_upgrade(int $oldversion): bool {
    // MDL-87790.
    mdl_index_helper::mtrace_tracker_issue("MDL-87790", "Global Search indexing very slow");
    mdl_index_helper::ensure_index('page', ['timemodified']);

    // MDL-87788.
    mdl_index_helper::mtrace_tracker_issue("MDL-87788", "Optimize completion aggregation query");
    mdl_index_helper::ensure_index('course_completions', ['reaggregate', 'timecompleted', 'course', 'userid']);

    // MDL-87670.

    // MDL-87650.
    mdl_index_helper::mtrace_tracker_issue("MDL-87650", "Forum report took Moodle down");
    mdl_index_helper::ensure_index('logstore_standard_log', ['anonymous', 'userid']);

    // MDL-87652.
    mdl_index_helper::mtrace_tracker_issue("MDL-87652", "Extremely slow queries");
    mdl_index_helper::ensure_index('role_assignments', ['contextid', 'userid']);

    mdl_index_helper::ensure_index('grade_grades', ['itemid', 'userid', 'finalgrade']);

    mdl_index_helper::ensure_index('grade_items', ['courseid', 'itemtype']);
    mdl_index_helper::ensure_index('grade_items', ['courseid', 'timemodified']);

    mdl_index_helper::ensure_index('course_completion_criteria', ['criteriatype', 'course']);

    mdl_index_helper::ensure_index('course_completion_crit_compl', ['criteriaid', 'userid']);

    return true;
}
