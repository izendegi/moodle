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
 * Upgrade file
 *
 * @package    mod_typeform
 * @copyright  2026 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Database tables upgrade
 *
 * @param $oldversion
 * @return true
 * @throws ddl_exception
 * @throws ddl_field_missing_exception
 * @throws ddl_table_missing_exception
 * @throws downgrade_exception
 * @throws moodle_exception
 * @throws upgrade_exception
 */
function xmldb_typeform_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();
    if ($oldversion < 2025011501) {
        // Define field completionsubmit to be added to typeform.
        $table = new xmldb_table('typeform');
        $field = new xmldb_field('completionsubmit', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'anonymous');

        // Conditionally launch add field completionsubmit.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Typeform savepoint reached.
        upgrade_mod_savepoint(true, 2025011501, 'typeform');
    }
    if ($oldversion < 2025011503) {
        // Define table typeform_submission to be created.
        $table = new xmldb_table('typeform_submission');

        // Adding fields to table typeform_submission.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('typeform', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('submitted', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table typeform_submission.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for typeform_submission.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Typeform savepoint reached.
        upgrade_mod_savepoint(true, 2025011503, 'typeform');
    }
    if ($oldversion < 2026042802) {
        // Add new column workspaceid
        $table = new xmldb_table('typeform');
        $field = new xmldb_field(
            'workspaceid',
            XMLDB_TYPE_CHAR,
            '255',
            null,
            XMLDB_NOTNULL,
            null,
            null,
            'typeformid'
        );
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2026042802, 'typeform');
    }
    if ($oldversion < 2026061800) {
        // Rename the "anonymous" field to "includestudentcode": the student_code value is now
        // always anonymised, and this flag only controls whether it is included in the Typeform URL.
        $table = new xmldb_table('typeform');
        $field = new xmldb_field(
            'anonymous',
            XMLDB_TYPE_INTEGER,
            '1',
            null,
            XMLDB_NOTNULL,
            null,
            '0',
            'typeformtitle'
        );
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'includestudentcode');
        }
        upgrade_mod_savepoint(true, 2026061800, 'typeform');
    }
    return true;
}
