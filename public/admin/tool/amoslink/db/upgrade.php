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
 * Upgrade file for AMOS Link tool.
 *
 * @package    tool_amoslink
 * @copyright  2024 Olivier VALENTIN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Execute tool_amoslink upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_tool_amoslink_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2024062500) {
        upgrade_plugin_savepoint(true, 2024062500, 'tool', 'amoslink');
    }
    if ($oldversion < 2024070100) {
        // Checks for PHP 8 function str_contains, and use a polyfill for PHP 7.
        upgrade_plugin_savepoint(true, 2024070100, 'tool', 'amoslink');
    }
    return true;
}
