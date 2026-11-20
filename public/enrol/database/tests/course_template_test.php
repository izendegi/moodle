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

namespace enrol_database;

/**
 * Tests for the creation of courses from a template course of the external database enrolment plugin.
 *
 * @package    enrol_database
 * @category   test
 * @copyright  2026 Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversMethod(\enrol_database_plugin::class, 'sync_courses')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\enrol_database_plugin::class, 'duplicate_template_course')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\enrol_database_plugin::class, 'restore_template_backup')]
final class course_template_test extends \advanced_testcase {
    /** @var string name of the external courses table, without prefix. */
    private const TABLE = 'enrol_database_test_newcourses';

    /** @var string original error log. */
    private $oldlog;

    public static function tearDownAfterClass(): void {
        global $DB;
        // Apply sqlsrv native driver error and logging default settings while finishing the AdoDB tests.
        if ($DB->get_dbfamily() === 'mssql') {
            sqlsrv_configure("WarningsReturnAsErrors", false);
            sqlsrv_configure("LogSubsystems", SQLSRV_LOG_SYSTEM_OFF);
            sqlsrv_configure("LogSeverity", SQLSRV_LOG_SEVERITY_ERROR);
        }
        parent::tearDownAfterClass();
    }

    /**
     * Points the plugin to the Moodle database as external database and creates the external courses table.
     */
    private function init_enrol_database(): void {
        global $CFG, $DB;

        // Discard error logs from AdoDB.
        $this->oldlog = ini_get('error_log');
        ini_set('error_log', "$CFG->dataroot/testlog.log");

        set_config('dbencoding', 'utf-8', 'enrol_database');
        set_config('dbhost', $CFG->dbhost, 'enrol_database');
        set_config('dbuser', $CFG->dbuser, 'enrol_database');
        set_config('dbpass', $CFG->dbpass, 'enrol_database');
        set_config('dbname', $CFG->dbname, 'enrol_database');
        if (!empty($CFG->dboptions['dbport'])) {
            set_config('dbhost', $CFG->dbhost . ':' . $CFG->dboptions['dbport'], 'enrol_database');
        }

        switch ($DB->get_dbfamily()) {
            case 'mysql':
                set_config('dbtype', 'mysqli', 'enrol_database');
                set_config('dbsetupsql', "SET NAMES 'UTF-8'", 'enrol_database');
                if (!empty($CFG->dboptions['dbsocket'])) {
                    $dbsocket = $CFG->dboptions['dbsocket'];
                    if ((strpos($dbsocket, '/') === false && strpos($dbsocket, '\\') === false)) {
                        $dbsocket = ini_get('mysqli.default_socket');
                    }
                    $dbtype = 'mysqli://' . rawurlencode($CFG->dbuser) . ':' . rawurlencode($CFG->dbpass) . '@' .
                        rawurlencode($CFG->dbhost) . '/' . rawurlencode($CFG->dbname) . '?socket=' . rawurlencode($dbsocket);
                    set_config('dbtype', $dbtype, 'enrol_database');
                }
                break;

            case 'postgres':
                set_config('dbtype', 'postgres7', 'enrol_database');
                $setupsql = "SET NAMES 'UTF-8'";
                if (!empty($CFG->dboptions['dbschema'])) {
                    $setupsql .= "; SET search_path = '" . $CFG->dboptions['dbschema'] . "'";
                }
                set_config('dbsetupsql', $setupsql, 'enrol_database');
                if (!empty($CFG->dboptions['dbsocket']) && ($CFG->dbhost === 'localhost' || $CFG->dbhost === '127.0.0.1')) {
                    if (strpos($CFG->dboptions['dbsocket'], '/') !== false) {
                        $socket = $CFG->dboptions['dbsocket'];
                        if (!empty($CFG->dboptions['dbport'])) {
                            $socket .= ':' . $CFG->dboptions['dbport'];
                        }
                        set_config('dbhost', $socket, 'enrol_database');
                    } else {
                        set_config('dbhost', '', 'enrol_database');
                    }
                }
                break;

            case 'mssql':
                set_config('dbtype', 'mssqlnative', 'enrol_database');
                // The native sqlsrv driver uses a comma as separator between host and port.
                $dbhost = $CFG->dbhost;
                if (!empty($CFG->dboptions['dbport'])) {
                    $dbhost .= ',' . $CFG->dboptions['dbport'];
                }
                set_config('dbhost', $dbhost, 'enrol_database');
                break;

            default:
                throw new \coding_exception('Unknown database driver ' . get_class($DB));
        }

        $dbman = $DB->get_manager();
        $table = new \xmldb_table(self::TABLE);
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('fullname', XMLDB_TYPE_CHAR, '255', null, null, null);
        $table->add_field('shortname', XMLDB_TYPE_CHAR, '255', null, null, null);
        $table->add_field('idnumber', XMLDB_TYPE_CHAR, '255', null, null, null);
        $table->add_field('summary', XMLDB_TYPE_CHAR, '255', null, null, null);
        $table->add_field('template', XMLDB_TYPE_CHAR, '255', null, null, null);
        $table->add_field('startdate', XMLDB_TYPE_CHAR, '255', null, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        $dbman->create_table($table);

        set_config('newcoursetable', $CFG->prefix . self::TABLE, 'enrol_database');
        set_config('newcoursefullname', 'fullname', 'enrol_database');
        set_config('newcourseshortname', 'shortname', 'enrol_database');
        set_config('newcourseidnumber', 'idnumber', 'enrol_database');
        set_config('newcoursetemplate', 'template', 'enrol_database');
        set_config('localtemplatefield', 'idnumber', 'enrol_database');
        set_config('newcoursestartdate', 'startdate', 'enrol_database');
        set_config('templatecourse', '', 'enrol_database');
    }

    /**
     * Drops the external table.
     */
    private function cleanup_enrol_database(): void {
        global $DB;

        $DB->get_manager()->drop_table(new \xmldb_table(self::TABLE));
        ini_set('error_log', $this->oldlog);
    }

    /**
     * Courses are created as a copy of their template, with the site default visibility and the external dates.
     */
    public function test_sync_courses_template(): void {
        global $DB;
        $this->resetAfterTest();
        $this->preventResetByRollback();
        $this->init_enrol_database();
        $plugin = enrol_get_plugin('database');
        set_config('visible', 1, 'moodlecourse');

        $generator = $this->getDataGenerator();
        $template = $generator->create_course(['shortname' => 'tpl', 'idnumber' => 'TPL', 'visible' => 0,
            'startdate' => mktime(0, 0, 0, 9, 1, 2025)]);
        $generator->create_module('page', ['course' => $template->id, 'name' => 'Template page']);
        $generator->create_group(['courseid' => $template->id, 'idnumber' => 'tplgroup']);
        $startdate = mktime(0, 0, 0, 9, 1, 2026);
        $DB->insert_record(self::TABLE, ['fullname' => 'New course 1', 'shortname' => 'new1', 'idnumber' => 'NEW1',
            'template' => 'TPL', 'startdate' => (string) $startdate]);
        $DB->insert_record(self::TABLE, ['fullname' => 'New course 2', 'shortname' => 'new2', 'idnumber' => 'NEW2',
            'template' => 'XXX', 'startdate' => (string) $startdate]);

        $this->assertSame(0, $plugin->sync_courses(new \null_progress_trace()));

        $course = $DB->get_record('course', ['shortname' => 'new1'], '*', MUST_EXIST);
        $this->assertEquals('NEW1', $course->idnumber);
        $this->assertEquals(1, $course->visible);
        $this->assertEquals($startdate, $course->startdate);
        $this->assertTrue($DB->record_exists('page', ['course' => $course->id, 'name' => 'Template page']));
        $this->assertFalse($DB->record_exists('groups', ['courseid' => $course->id]));

        // An unknown template uses the default template, without copying any course.
        $course = $DB->get_record('course', ['shortname' => 'new2'], '*', MUST_EXIST);
        $this->assertEquals('NEW2', $course->idnumber);
        $this->assertFalse($DB->record_exists('page', ['course' => $course->id]));

        $this->cleanup_enrol_database();
    }

    /**
     * The default template ('New course template' setting) is copied with its activities, without groups, for the courses
     * without template and for the courses whose template is not found. A template found has priority over it.
     */
    public function test_sync_courses_default_template(): void {
        global $DB;
        $this->resetAfterTest();
        $this->preventResetByRollback();
        $this->init_enrol_database();
        $plugin = enrol_get_plugin('database');
        $plugin->set_config('templatecourse', 'deftpl');

        $generator = $this->getDataGenerator();
        $default = $generator->create_course(['shortname' => 'deftpl']);
        $generator->create_module('page', ['course' => $default->id, 'name' => 'Default page']);
        $generator->create_group(['courseid' => $default->id]);
        $template = $generator->create_course(['shortname' => 'tpl', 'idnumber' => 'TPL']);
        $generator->create_module('page', ['course' => $template->id, 'name' => 'Template page']);
        $DB->insert_record(self::TABLE, ['fullname' => 'New course 1', 'shortname' => 'new1', 'idnumber' => 'NEW1']);
        $DB->insert_record(self::TABLE, ['fullname' => 'New course 2', 'shortname' => 'new2', 'idnumber' => 'NEW2',
            'template' => 'XXX']);
        $DB->insert_record(self::TABLE, ['fullname' => 'New course 3', 'shortname' => 'new3', 'idnumber' => 'NEW3',
            'template' => 'TPL']);

        $this->assertSame(0, $plugin->sync_courses(new \null_progress_trace()));
        $courseid = fn(string $shortname) => $DB->get_field('course', 'id', ['shortname' => $shortname], MUST_EXIST);
        $pages = fn(string $shortname) => $DB->get_fieldset('page', 'name', ['course' => $courseid($shortname)]);
        $this->assertEquals(['Default page'], $pages('new1'));
        $this->assertEquals(['Default page'], $pages('new2'));
        $this->assertEquals(['Template page'], $pages('new3'));
        foreach (['new1', 'new2'] as $shortname) {
            $course = $DB->get_record('course', ['shortname' => $shortname], '*', MUST_EXIST);
            $this->assertEquals(strtoupper($shortname), $course->idnumber);
            $this->assertFalse($DB->record_exists('groups', ['courseid' => $course->id]));
        }

        $this->cleanup_enrol_database();
    }

    /**
     * The summary of the new courses is taken from the external table, with or without template: the summary of the
     * template is not kept.
     */
    public function test_sync_courses_summary(): void {
        global $DB;
        $this->resetAfterTest();
        $this->preventResetByRollback();
        $this->init_enrol_database();
        $plugin = enrol_get_plugin('database');
        $plugin->set_config('newcoursesummary', 'summary');

        $this->getDataGenerator()->create_course(['shortname' => 'tpl', 'idnumber' => 'TPL',
            'summary' => 'Template summary']);
        $DB->insert_record(self::TABLE, ['fullname' => 'New course 1', 'shortname' => 'new1', 'idnumber' => 'NEW1',
            'template' => 'TPL', 'summary' => 'Summary 1']);
        $DB->insert_record(self::TABLE, ['fullname' => 'New course 2', 'shortname' => 'new2', 'idnumber' => 'NEW2',
            'summary' => 'Summary 2']);

        $this->assertSame(0, $plugin->sync_courses(new \null_progress_trace()));
        $this->assertEquals('Summary 1', $DB->get_field('course', 'summary', ['shortname' => 'new1'], MUST_EXIST));
        $this->assertEquals('Summary 2', $DB->get_field('course', 'summary', ['shortname' => 'new2'], MUST_EXIST));
        $this->assertEquals('Template summary', $DB->get_field('course', 'summary', ['shortname' => 'tpl']));

        $this->cleanup_enrol_database();
    }

    /**
     * The template is looked up by idnumber if the 'Local template field' setting is not set or has an invalid value.
     */
    public function test_sync_courses_template_local_field_default(): void {
        global $DB;
        $this->resetAfterTest();
        $this->preventResetByRollback();
        $this->init_enrol_database();

        $template = $this->getDataGenerator()->create_course(['shortname' => 'tpl', 'idnumber' => 'TPL']);
        $this->getDataGenerator()->create_module('page', ['course' => $template->id, 'name' => 'Template page']);
        $courseid = fn(string $shortname) => $DB->get_field('course', 'id', ['shortname' => $shortname], MUST_EXIST);
        $page = fn(string $shortname) => $DB->record_exists('page', ['course' => $courseid($shortname), 'name' => 'Template page']);

        // Not set.
        unset_config('localtemplatefield', 'enrol_database');
        $DB->insert_record(self::TABLE, ['fullname' => 'New course 1', 'shortname' => 'new1', 'idnumber' => 'NEW1',
            'template' => 'TPL']);
        $this->assertSame(0, enrol_get_plugin('database')->sync_courses(new \null_progress_trace()));
        $this->assertTrue($page('new1'));

        // Invalid value.
        set_config('localtemplatefield', 'fullname', 'enrol_database');
        $DB->insert_record(self::TABLE, ['fullname' => 'New course 2', 'shortname' => 'new2', 'idnumber' => 'NEW2',
            'template' => 'TPL']);
        $this->assertSame(0, enrol_get_plugin('database')->sync_courses(new \null_progress_trace()));
        $this->assertTrue($page('new2'));

        $this->cleanup_enrol_database();
    }

    /**
     * The optional settings may not be saved and the template field may be NULL: no PHP deprecation is raised.
     */
    public function test_sync_courses_unset_optional_settings(): void {
        global $DB;
        $this->resetAfterTest();
        $this->preventResetByRollback();
        $this->init_enrol_database();
        unset_config('newcoursesummary', 'enrol_database');
        unset_config('newcoursecategorypath', 'enrol_database');

        // PHP deprecations do not make the test fail, so they are collected.
        $deprecations = [];
        set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) use (&$deprecations): bool {
            $deprecations[] = "$errstr ($errfile:$errline)";
            return true;
        }, E_DEPRECATED);
        try {
            // The template field is configured, but it is NULL in the external table.
            $DB->insert_record(self::TABLE, ['fullname' => 'New course 1', 'shortname' => 'new1', 'idnumber' => 'NEW1']);
            $this->assertSame(0, enrol_get_plugin('database')->sync_courses(new \null_progress_trace()));

            // The template field is not configured either.
            unset_config('newcoursetemplate', 'enrol_database');
            $DB->insert_record(self::TABLE, ['fullname' => 'New course 2', 'shortname' => 'new2', 'idnumber' => 'NEW2']);
            $this->assertSame(0, enrol_get_plugin('database')->sync_courses(new \null_progress_trace()));
        } finally {
            restore_error_handler();
        }

        $this->assertTrue($DB->record_exists('course', ['shortname' => 'new1']));
        $this->assertTrue($DB->record_exists('course', ['shortname' => 'new2']));
        $plugindir = 'enrol' . DIRECTORY_SEPARATOR . 'database';
        $plugindeprecations = array_filter($deprecations, fn(string $deprecation) => str_contains($deprecation, $plugindir));
        $this->assertSame([], array_values($plugindeprecations));

        $this->cleanup_enrol_database();
    }

    /**
     * The copy of the template runs as admin whatever the current user is, and the current user is restored after it.
     */
    public function test_sync_courses_template_current_user(): void {
        global $DB, $USER;
        $this->resetAfterTest();
        $this->preventResetByRollback();
        $this->init_enrol_database();
        $plugin = enrol_get_plugin('database');

        $generator = $this->getDataGenerator();
        $generator->create_course(['shortname' => 'tpl', 'idnumber' => 'TPL']);
        $DB->insert_record(self::TABLE, ['fullname' => 'New course 1', 'shortname' => 'new1', 'idnumber' => 'NEW1',
            'template' => 'TPL']);

        // A user without permissions to create courses.
        $user = $generator->create_user();
        $this->setUser($user);

        $this->assertSame(0, $plugin->sync_courses(new \null_progress_trace()));
        $this->assertTrue($DB->record_exists('course', ['shortname' => 'new1', 'idnumber' => 'NEW1']));
        $this->assertEquals($user->id, $USER->id);
        $this->assertFalse(is_siteadmin());

        $this->cleanup_enrol_database();
    }

    /**
     * The template is copied with its activities, but without users, groups nor groupings, and the backup file is removed.
     */
    public function test_duplicate_template_course(): void {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/group/lib.php');
        $this->resetAfterTest();
        $this->setAdminUser();
        $plugin = new class extends \enrol_database_plugin {
            /**
             * Calls the protected method duplicate_template_course().
             *
             * @param mixed ...$args
             * @return int
             */
            public function duplicate(...$args): int {
                return $this->duplicate_template_course(...$args);
            }
        };

        $generator = $this->getDataGenerator();
        $category = $generator->create_category();
        $template = $generator->create_course(['shortname' => 'tpl', 'visible' => 0]);
        $generator->create_module('page', ['course' => $template->id, 'name' => 'Template page']);
        $group = $generator->create_group(['courseid' => $template->id]);
        $grouping = $generator->create_grouping(['courseid' => $template->id]);
        groups_assign_grouping($grouping->id, $group->id);
        $student = $generator->create_and_enrol($template, 'student');
        groups_add_member($group->id, $student->id);

        $courseid = $plugin->duplicate((int) $template->id, 'Copy 1', 'copy1', (int) $category->id, 1);

        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
        $this->assertEquals('Copy 1', $course->fullname);
        $this->assertEquals('copy1', $course->shortname);
        $this->assertEquals($category->id, $course->category);
        $this->assertEquals(1, $course->visible);
        $this->assertTrue($DB->record_exists('page', ['course' => $courseid, 'name' => 'Template page']));
        $this->assertFalse($DB->record_exists('groups', ['courseid' => $courseid]));
        $this->assertFalse($DB->record_exists('groupings', ['courseid' => $courseid]));
        $this->assertEmpty(enrol_get_course_users($courseid));
        // The template is not changed and the backup file is not kept in it.
        $this->assertTrue($DB->record_exists('groups', ['id' => $group->id]));
        $select = "component = 'backup' AND filename <> '.' AND contextid = ?";
        $this->assertFalse($DB->record_exists_select('files', $select, [\context_course::instance($template->id)->id]));

        // If the copy fails, no course is left.
        try {
            $plugin->duplicate(-1, 'Copy 2', 'copy2', (int) $category->id, 1);
            $this->fail('An exception was expected');
        } catch (\moodle_exception $e) {
            $this->assertFalse($DB->record_exists('course', ['shortname' => 'copy2']));
        }
    }

    /**
     * If the restore fails once the new course has been created, the incomplete course is removed, so that it does not
     * block its shortname, and the backup file is not kept in the template.
     */
    public function test_duplicate_template_course_restore_error(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $plugin = new class extends \enrol_database_plugin {
            /**
             * Calls the protected method duplicate_template_course().
             *
             * @param mixed ...$args
             * @return int
             */
            public function duplicate(...$args): int {
                return $this->duplicate_template_course(...$args);
            }

            /**
             * Fails as a restore that breaks once the new course exists.
             *
             * @param string $backupid
             * @param int $newcourseid
             * @param int $userid
             * @param array $settings
             * @return void
             */
            protected function restore_template_backup(
                string $backupid,
                int $newcourseid,
                int $userid,
                array $settings
            ): void {
                global $DB;
                if (!$DB->record_exists('course', ['id' => $newcourseid])) {
                    throw new \coding_exception('The new course should exist when it is restored');
                }
                throw new \moodle_exception('error');
            }
        };

        $generator = $this->getDataGenerator();
        $category = $generator->create_category();
        $template = $generator->create_course(['shortname' => 'tpl']);
        $generator->create_module('page', ['course' => $template->id]);
        $count = $DB->count_records('course');

        try {
            $plugin->duplicate((int) $template->id, 'Copy 3', 'copy3', (int) $category->id, 1);
            $this->fail('An exception was expected');
        } catch (\moodle_exception $e) {
            $this->assertNotInstanceOf(\coding_exception::class, $e);
        }
        $this->assertFalse($DB->record_exists('course', ['shortname' => 'copy3']));
        $this->assertEquals($count, $DB->count_records('course'));
        $select = "component = 'backup' AND filename <> '.' AND contextid = ?";
        $this->assertFalse($DB->record_exists_select('files', $select, [\context_course::instance($template->id)->id]));
    }
}
