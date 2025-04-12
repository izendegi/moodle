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
 * Database upgrade scripts
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package    block_user_favorites
 * @copyright  2019-06-24 Mfreak.nl | LdesignMedia.nl - Luuk Verhoeven
 * @author     Luuk Verhoeven
 **/

/**
 * xmldb_block_user_favorites_upgrade
 *
 * @param int $oldversion
 *
 * @return bool
 */
function xmldb_block_user_favorites_upgrade($oldversion) {

    global $DB;
    $dbman = $DB->get_manager();
    if ($oldversion < 2019062400) {

        // Define table block_user_favorites to be created.
        $table = new xmldb_table('block_user_favorites');

        // Adding fields to table block_user_favorites.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('title', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('url', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('hash', XMLDB_TYPE_CHAR, '33', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('created_at', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table block_user_favorites.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Adding indexes to table block_user_favorites.
        $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, ['userid']);

        // Conditionally launch create table for block_user_favorites.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        \block_user_favorites\helper::convert_users_preference_favorites();

        // Timeline savepoint reached.
        upgrade_block_savepoint(true, 2019062400, 'user_favorites');
    }

    return true;
}
