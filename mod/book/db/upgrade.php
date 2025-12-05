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
 * Book module upgrade code
 *
 * @package    mod_book
 * @copyright  2009-2011 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Book module upgrade task
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool always true
 */
function xmldb_book_upgrade($oldversion) {
    // Automatically generated Moodle v4.2.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v4.3.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v4.4.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v4.5.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v5.0.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2025041401) {

        // Changing precision of field name on table book to (1333).
        $table = new xmldb_table('book');
        $field = new xmldb_field('name', XMLDB_TYPE_CHAR, '1333', null, XMLDB_NOTNULL, null, null, 'course');

        // Launch change of precision for field name.
        $dbman->change_field_precision($table, $field);

        // Changing precision of field title on table book_chapters to (1333).
        $table = new xmldb_table('book_chapters');
        $field = new xmldb_field('title', XMLDB_TYPE_CHAR, '1333', null, XMLDB_NOTNULL, null, null, 'subchapter');

        // Launch change of precision for field title.
        $dbman->change_field_precision($table, $field);

        // Book savepoint reached.
        upgrade_mod_savepoint(true, 2025041401, 'book');
    }

    if ($oldversion < 2025100600) {
        // Adds the new field to the user completion criteria.
        $table = new xmldb_table('book');
        $field = new xmldb_field('readpercent', XMLDB_TYPE_INTEGER, '4', null, false, null, '0', 'revision');

        // Conditionally launch add field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define table book_chapters_userviews to be created.
        $table = new xmldb_table('book_chapters_userviews');

        // Adding fields to table book_chapters_userviews.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('chapterid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10');

        // Adding keys to table book_chapters_userviews.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('chapterid', XMLDB_KEY_FOREIGN, ['chapterid'], 'book_chapters', ['id']);

        // Conditionally launch create table for book_chapters_userviews.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Book savepoint reached.
        upgrade_mod_savepoint(true, 2025100600, 'book');
    }

    // Automatically generated Moodle v5.1.0 release upgrade line.
    // Put any upgrade step following this.

    return true;
}
