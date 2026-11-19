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
 * Tests for the group membership synchronisation of the external database enrolment plugin.
 *
 * @package    enrol_database
 * @category   test
 * @copyright  2026 Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversMethod(\enrol_database_plugin::class, 'sync_groups')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\enrol_database_plugin::class, 'sync_group_enrolments')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\enrol_database_plugin::class, 'sync_user_group_enrolments')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\enrol_database_plugin::class, 'sync_user_enrolments')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\enrol_database_plugin::class, 'sync_group_memberships')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\enrol_database_plugin::class, 'get_ids_by_field')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\enrol_database_plugin::class, 'add_group_member')]
#[\PHPUnit\Framework\Attributes\CoversClass(\enrol_database\task\sync_enrolments::class)]
final class group_sync_test extends \advanced_testcase {

    /** @var string name of the external group enrolments table, without prefix. */
    private const TABLE = 'enrol_database_test_groupenrols';

    /** @var string name of the external groups table, without prefix. */
    private const GROUPSTABLE = 'enrol_database_test_groups';

    /** @var \stdClass[] test courses, indexed from 1. */
    private array $courses = [];

    /** @var \stdClass[] test users, indexed from 1. */
    private array $users = [];

    /** @var \stdClass[] test groups, indexed by name. */
    private array $groups = [];

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
     * Points the plugin to the Moodle database as external database and creates the external table and test data.
     *
     * Two courses (shortnames tc1 and tc2) with groups grp1 and grp2 in tc1 and grp1 in tc2, and four users
     * (idnumbers userid1 to userid4). All users are enrolled in both courses except user 4, who is not enrolled in tc1.
     */
    private function init_enrol_database(): void {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/group/lib.php');

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
                    if ((strpos($dbsocket, '/') === false and strpos($dbsocket, '\\') === false)) {
                        $dbsocket = ini_get('mysqli.default_socket');
                    }
                    set_config('dbtype', 'mysqli://' . rawurlencode($CFG->dbuser) . ':' . rawurlencode($CFG->dbpass) . '@' .
                        rawurlencode($CFG->dbhost) . '/' . rawurlencode($CFG->dbname) . '?socket=' . rawurlencode($dbsocket),
                        'enrol_database');
                }
                break;

            case 'postgres':
                set_config('dbtype', 'postgres7', 'enrol_database');
                $setupsql = "SET NAMES 'UTF-8'";
                if (!empty($CFG->dboptions['dbschema'])) {
                    $setupsql .= "; SET search_path = '" . $CFG->dboptions['dbschema'] . "'";
                }
                set_config('dbsetupsql', $setupsql, 'enrol_database');
                if (!empty($CFG->dboptions['dbsocket']) and ($CFG->dbhost === 'localhost' or $CFG->dbhost === '127.0.0.1')) {
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

        // NOTE: It is strongly discouraged to create new tables in advanced_testcase classes, but there is no other
        // simple way to test the external database sync, so transactions are disabled and the table is dropped at the end.
        $dbman = $DB->get_manager();
        $table = new \xmldb_table(self::TABLE);
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_CHAR, '255', null, null, null);
        $table->add_field('courseid', XMLDB_TYPE_CHAR, '255', null, null, null);
        $table->add_field('groupid', XMLDB_TYPE_CHAR, '255', null, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        $dbman->create_table($table);

        $table = new \xmldb_table(self::GROUPSTABLE);
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, null, null);
        $table->add_field('idnumber', XMLDB_TYPE_CHAR, '255', null, null, null);
        $table->add_field('courseid', XMLDB_TYPE_CHAR, '255', null, null, null);
        $table->add_field('groupingcode', XMLDB_TYPE_CHAR, '255', null, null, null);
        $table->add_field('description', XMLDB_TYPE_CHAR, '255', null, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        $dbman->create_table($table);

        set_config('groupenroltable', $CFG->prefix . self::TABLE, 'enrol_database');
        set_config('userfield', 'userid', 'enrol_database');
        set_config('newgroupcourse', 'courseid', 'enrol_database');
        set_config('groupfield', 'groupid', 'enrol_database');
        set_config('localuserfield', 'idnumber', 'enrol_database');
        set_config('localcoursefield', 'shortname', 'enrol_database');
        set_config('groupupgrading', 0, 'enrol_database');

        $generator = $this->getDataGenerator();
        for ($i = 1; $i <= 2; $i++) {
            $this->courses[$i] = $generator->create_course(['shortname' => 'tc' . $i, 'idnumber' => 'courseid' . $i]);
        }
        $this->groups['c1grp1'] = $generator->create_group(['courseid' => $this->courses[1]->id, 'idnumber' => 'grp1']);
        $this->groups['c1grp2'] = $generator->create_group(['courseid' => $this->courses[1]->id, 'idnumber' => 'grp2']);
        $this->groups['c2grp1'] = $generator->create_group(['courseid' => $this->courses[2]->id, 'idnumber' => 'grp1']);
        for ($i = 1; $i <= 4; $i++) {
            $this->users[$i] = $generator->create_user(['username' => 'username' . $i, 'idnumber' => 'userid' . $i]);
            foreach ($this->courses as $index => $course) {
                if ($i != 4 or $index != 1) {
                    $generator->enrol_user($this->users[$i]->id, $course->id);
                }
            }
        }
    }

    /**
     * Drops the external table.
     */
    private function cleanup_enrol_database(): void {
        global $DB;

        $DB->get_manager()->drop_table(new \xmldb_table(self::TABLE));
        $DB->get_manager()->drop_table(new \xmldb_table(self::GROUPSTABLE));
        ini_set('error_log', $this->oldlog);
    }

    /**
     * Adds a row to the external table.
     *
     * @param string $user external user identifier.
     * @param string $course external course identifier.
     * @param string $group external group identifier.
     */
    private function add_external(string $user, string $course, string $group): void {
        global $DB;
        $DB->insert_record(self::TABLE, ['userid' => $user, 'courseid' => $course, 'groupid' => $group]);
    }

    /**
     * Removes a row from the external table.
     *
     * @param string $user external user identifier.
     * @param string $course external course identifier.
     * @param string $group external group identifier.
     */
    private function remove_external(string $user, string $course, string $group): void {
        global $DB;
        $DB->delete_records(self::TABLE, ['userid' => $user, 'courseid' => $course, 'groupid' => $group]);
    }

    /**
     * Moves back the time the current members were added, as if the previous synchronisation had run some time ago.
     *
     * Members added in the same second the synchronisation starts are not removed by it.
     */
    private function age_members(): void {
        global $DB;
        $DB->execute('UPDATE {groups_members} SET timeadded = timeadded - 10');
    }

    /**
     * Returns the component of the membership of a user in a group, false if the user is not a member.
     *
     * @param string $group group name in $this->groups.
     * @param int $user user index in $this->users.
     * @return string|false
     */
    private function membership(string $group, int $user) {
        global $DB;
        return $DB->get_field('groups_members', 'component',
            ['groupid' => $this->groups[$group]->id, 'userid' => $this->users[$user]->id]);
    }

    /**
     * Groups are created in their course, and added to their grouping.
     */
    public function test_sync_groups(): void {
        global $CFG, $DB;
        $this->resetAfterTest();
        $this->preventResetByRollback();
        $this->init_enrol_database();
        $plugin = enrol_get_plugin('database');
        $trace = new \null_progress_trace();
        $plugin->set_config('newgrouptable', $CFG->prefix . self::GROUPSTABLE);
        $plugin->set_config('newgroupname', 'name');
        $plugin->set_config('newgroupidnumber', 'idnumber');
        $plugin->set_config('newgroupgroupings', 'groupingcode');
        $plugin->set_config('groupingcreation', 0);
        $plugin->set_config('groupmessaging', 1);
        // The scheduled task runs as admin, who can create group conversations.
        $this->setAdminUser();
        $addgroup = fn(string $name, string $idnumber, string $course, string $grouping = '') =>
            $DB->insert_record(self::GROUPSTABLE, ['name' => $name, 'idnumber' => $idnumber, 'courseid' => $course,
                'groupingcode' => $grouping]);
        $group = fn(int $course, string $idnumber) => $DB->get_record('groups',
            ['courseid' => $this->courses[$course]->id, 'idnumber' => $idnumber]);

        $addgroup('Group 1', 'grp1', 'tc1'); // Already exists.
        $addgroup('Group A', 'ga', 'tc1');
        $addgroup('Group A', 'ga', 'tc2', 'gr1'); // Same idnumber in another course.
        $addgroup('Group X', 'gx', 'xxx'); // Unknown course.
        $existinggrouping = $this->getDataGenerator()->create_grouping(['courseid' => $this->courses[2]->id,
            'idnumber' => 'gr1']);

        $this->assertSame(0, $plugin->sync_groups($trace));
        $this->assertEquals(2, $DB->count_records('groups', ['idnumber' => 'grp1']));
        $this->assertNotFalse($group(1, 'ga'));
        $this->assertSame('', $group(1, 'ga')->description);
        $this->assertNotFalse($group(2, 'ga'));
        $this->assertFalse($DB->record_exists('groups', ['idnumber' => 'gx']));
        $this->assertTrue($DB->record_exists('groupings_groups',
            ['groupingid' => $existinggrouping->id, 'groupid' => $group(2, 'ga')->id]));

        // A grouping is only looked up in the course of the group, and it is created if enabled.
        $addgroup('Group B', 'gb', 'tc1', 'gr1');
        $plugin->sync_groups($trace);
        $this->assertNotFalse($group(1, 'gb'));
        $this->assertFalse($DB->record_exists('groupings_groups', ['groupid' => $group(1, 'gb')->id]));
        $plugin->set_config('groupingcreation', 1);
        $addgroup('Group C', 'gc', 'tc1', 'gr1');
        $plugin->sync_groups($trace);
        $newgrouping = $DB->get_record('groupings', ['courseid' => $this->courses[1]->id, 'idnumber' => 'gr1'], '*', MUST_EXIST);
        $this->assertTrue($DB->record_exists('groupings_groups',
            ['groupingid' => $newgrouping->id, 'groupid' => $group(1, 'gc')->id]));
        $this->assertEquals(1, $DB->count_records('groupings', ['idnumber' => 'gr1', 'courseid' => $this->courses[2]->id]));

        // Existing groups keep their name (teachers may rename them) and, if the field is configured, take the
        // description of the external table, unless it is empty.
        $c2ga = $group(2, 'ga');
        $c1ga = $group(1, 'ga');
        $DB->set_field(self::GROUPSTABLE, 'name', 'Group A renamed', ['idnumber' => 'ga', 'courseid' => 'tc1']);
        $DB->set_field(self::GROUPSTABLE, 'description', 'New description', ['idnumber' => 'ga', 'courseid' => 'tc1']);
        $plugin->sync_groups($trace);
        $this->assertEquals($c1ga, $group(1, 'ga'));
        $plugin->set_config('newgroupdesc', 'description');
        $grp1description = $group(1, 'grp1')->description;
        $this->assertNotEmpty($grp1description);
        $sink = $this->redirectEvents();
        $plugin->sync_groups($trace);
        $this->assertEquals('Group A', $group(1, 'ga')->name);
        $this->assertEquals('New description', $group(1, 'ga')->description);
        $this->assertEquals($grp1description, $group(1, 'grp1')->description);
        $this->assertEquals($c2ga, $group(2, 'ga'));
        // Only the group whose description changed is updated.
        $updated = array_filter($sink->get_events(), fn($event) => $event instanceof \core\event\group_updated);
        $this->assertEquals([$c1ga->id], array_map(fn($event) => $event->objectid, array_values($updated)));
        $sink->close();

        // The group conversation is not disabled by the update.
        $this->assertEquals(1, $DB->get_field('message_conversations', 'enabled',
            ['component' => 'core_group', 'itemtype' => 'groups', 'itemid' => $c1ga->id], MUST_EXIST));
        $sink = $this->redirectEvents();
        $plugin->sync_groups($trace);
        $this->assertCount(0, array_filter($sink->get_events(), fn($event) => $event instanceof \core\event\group_updated));
        $sink->close();

        $this->cleanup_enrol_database();
    }

    /**
     * The optional settings of the groups synchronisation may not be saved: no PHP deprecation is raised.
     */
    public function test_sync_groups_unset_optional_settings(): void {
        global $CFG, $DB;
        $this->resetAfterTest();
        $this->preventResetByRollback();
        $this->init_enrol_database();
        $plugin = enrol_get_plugin('database');
        $plugin->set_config('newgrouptable', $CFG->prefix . self::GROUPSTABLE);
        $plugin->set_config('newgroupname', 'name');
        $plugin->set_config('newgroupidnumber', 'idnumber');
        $plugin->set_config('groupmessaging', 0);
        unset_config('newgroupdesc', 'enrol_database');
        unset_config('newgroupgroupings', 'enrol_database');
        $DB->insert_record(self::GROUPSTABLE, ['name' => 'Group A', 'idnumber' => 'ga', 'courseid' => 'tc1']);

        // PHP deprecations do not make the test fail, so they are collected.
        $deprecations = [];
        set_error_handler(function(int $errno, string $errstr, string $errfile, int $errline) use (&$deprecations): bool {
            $deprecations[] = "$errstr ($errfile:$errline)";
            return true;
        }, E_DEPRECATED);
        try {
            $this->assertSame(0, enrol_get_plugin('database')->sync_groups(new \null_progress_trace()));
        } finally {
            restore_error_handler();
        }

        $this->assertTrue($DB->record_exists('groups', ['courseid' => $this->courses[1]->id, 'idnumber' => 'ga']));
        $this->assertSame([], array_values(array_filter($deprecations,
            fn(string $deprecation) => str_contains($deprecation, 'enrol' . DIRECTORY_SEPARATOR . 'database'))));

        $this->cleanup_enrol_database();
    }

    /**
     * Groups members are added and removed by the scheduled synchronisation.
     */
    public function test_sync_group_enrolments(): void {
        global $DB;
        $this->resetAfterTest();
        $this->preventResetByRollback();
        $this->init_enrol_database();
        $plugin = enrol_get_plugin('database');
        $trace = new \null_progress_trace();

        $this->add_external('userid1', 'tc1', 'grp1');
        $this->add_external('userid1', 'tc1', 'grp1'); // Duplicated rows are fine.
        $this->add_external('userid2', 'tc1', 'grp1');
        $this->add_external('userid1', 'tc2', 'grp1');
        $this->add_external('userid3', 'tc1', 'grp2');
        $this->add_external('userid4', 'tc1', 'grp2'); // Not enrolled in the course, can not be added.
        $this->add_external('xxxxxxx', 'tc1', 'grp1'); // Unknown user.
        $this->add_external('userid2', 'xxx', 'grp1'); // Unknown course.
        $this->add_external('userid2', 'tc1', 'xxxx'); // Unknown group.
        $this->add_external('userid3', 'tc2', 'grp2'); // Group of another course.

        $this->assertSame(0, $plugin->sync_group_enrolments($trace));
        $this->assertSame('enrol_database', $this->membership('c1grp1', 1));
        $this->assertSame('enrol_database', $this->membership('c1grp1', 2));
        $this->assertSame('enrol_database', $this->membership('c2grp1', 1));
        $this->assertSame('enrol_database', $this->membership('c1grp2', 3));
        $this->assertFalse($this->membership('c1grp2', 4));
        $this->assertFalse($this->membership('c2grp1', 2));
        $this->assertFalse($this->membership('c2grp1', 3));
        $this->assertEquals(4, $DB->count_records('groups_members'));

        // A second run does not change anything.
        $plugin->sync_group_enrolments($trace);
        $this->assertEquals(4, $DB->count_records('groups_members'));

        // Members removed from the external table are removed from the group, only in the course of the removed row.
        $this->remove_external('userid1', 'tc2', 'grp1');
        $this->remove_external('userid3', 'tc1', 'grp2');
        $this->age_members();
        $plugin->sync_group_enrolments($trace);
        $this->assertFalse($this->membership('c2grp1', 1));
        $this->assertFalse($this->membership('c1grp2', 3));
        $this->assertSame('enrol_database', $this->membership('c1grp1', 1));
        $this->assertSame('enrol_database', $this->membership('c1grp1', 2));

        // Members not added by this plugin are never removed.
        groups_add_member($this->groups['c2grp1']->id, $this->users[3]->id);
        groups_add_member($this->groups['c1grp2']->id, $this->users[2]->id, 'enrol_self');
        $plugin->sync_group_enrolments($trace);
        $this->assertSame('', $this->membership('c2grp1', 3));
        $this->assertSame('enrol_self', $this->membership('c1grp2', 2));

        $this->cleanup_enrol_database();
    }

    /**
     * Manually added members are converted to external database members only if groupupgrading is enabled.
     */
    public function test_sync_group_enrolments_groupupgrading(): void {
        global $DB;
        $this->resetAfterTest();
        $this->preventResetByRollback();
        $this->init_enrol_database();
        $plugin = enrol_get_plugin('database');
        $trace = new \null_progress_trace();

        groups_add_member($this->groups['c1grp1']->id, $this->users[1]->id);
        groups_add_member($this->groups['c1grp1']->id, $this->users[2]->id, 'enrol_self');
        $this->add_external('userid1', 'tc1', 'grp1');
        $this->add_external('userid2', 'tc1', 'grp1');

        $plugin->sync_group_enrolments($trace);
        $this->assertSame('', $this->membership('c1grp1', 1));
        $this->assertSame('enrol_self', $this->membership('c1grp1', 2));

        $instanceid = $plugin->add_instance($this->courses[1]);
        $before = $DB->get_record('groups_members', ['groupid' => $this->groups['c1grp1']->id, 'userid' => $this->users[1]->id]);
        $plugin->set_config('groupupgrading', 1);
        $sink = $this->redirectEvents();
        $plugin->sync_group_enrolments($trace);
        $events = $sink->get_events();
        $sink->close();
        $this->assertSame('enrol_database', $this->membership('c1grp1', 1));
        $this->assertSame('enrol_self', $this->membership('c1grp1', 2));

        // The membership is updated in place: same record, same time added and no group membership events.
        $after = $DB->get_record('groups_members', ['groupid' => $this->groups['c1grp1']->id, 'userid' => $this->users[1]->id]);
        $this->assertEquals($before->id, $after->id);
        $this->assertEquals($before->timeadded, $after->timeadded);
        $this->assertEquals($instanceid, $after->itemid);
        foreach ($events as $event) {
            $this->assertNotInstanceOf(\core\event\group_member_removed::class, $event);
            $this->assertNotInstanceOf(\core\event\group_member_added::class, $event);
        }

        $this->cleanup_enrol_database();
    }

    /**
     * The synchronisation on login only changes the memberships of the user logging in.
     */
    public function test_sync_user_group_enrolments(): void {
        global $DB;
        $this->resetAfterTest();
        $this->preventResetByRollback();
        $this->init_enrol_database();
        $plugin = enrol_get_plugin('database');

        // Another user with the same idnumber as user 1.
        $this->users[5] = $this->getDataGenerator()->create_user(['username' => 'username5', 'idnumber' => 'userid1']);
        $this->getDataGenerator()->enrol_user($this->users[5]->id, $this->courses[1]->id);

        $this->add_external('userid1', 'tc1', 'grp1');
        $this->add_external('userid1', 'tc2', 'grp1');
        $this->add_external('userid2', 'tc1', 'grp1');

        $plugin->sync_user_group_enrolments($this->users[1]);
        $this->assertSame('enrol_database', $this->membership('c1grp1', 1));
        $this->assertSame('enrol_database', $this->membership('c2grp1', 1));
        $this->assertFalse($this->membership('c1grp1', 2));
        $this->assertFalse($this->membership('c1grp1', 5));
        $this->assertEquals(2, $DB->count_records('groups_members'));

        // Only the memberships of the user logging in are removed.
        $plugin->sync_user_group_enrolments($this->users[2]);
        $this->remove_external('userid1', 'tc2', 'grp1');
        $this->remove_external('userid2', 'tc1', 'grp1');
        $this->age_members();
        $plugin->sync_user_group_enrolments($this->users[1]);
        $this->assertFalse($this->membership('c2grp1', 1));
        $this->assertSame('enrol_database', $this->membership('c1grp1', 1));
        $this->assertSame('enrol_database', $this->membership('c1grp1', 2));

        $plugin->sync_user_group_enrolments($this->users[2]);
        $this->assertFalse($this->membership('c1grp1', 2));

        $this->cleanup_enrol_database();
    }

    /**
     * If the external table has no valid rows, the scheduled synchronisation does not remove any member, but the
     * synchronisation of a single user does.
     */
    public function test_sync_group_enrolments_empty_table(): void {
        global $DB;
        $this->resetAfterTest();
        $this->preventResetByRollback();
        $this->init_enrol_database();
        $plugin = enrol_get_plugin('database');

        groups_add_member($this->groups['c1grp1']->id, $this->users[1]->id, 'enrol_database');
        groups_add_member($this->groups['c1grp1']->id, $this->users[2]->id, 'enrol_database');
        $this->age_members();

        // Empty table.
        $trace = new \progress_trace_buffer(new \text_progress_trace(), false);
        $this->assertSame(0, $plugin->sync_group_enrolments($trace));
        $this->assertStringContainsString('no group members are removed', $trace->get_buffer());
        $this->assertSame('enrol_database', $this->membership('c1grp1', 1));
        $this->assertSame('enrol_database', $this->membership('c1grp1', 2));

        // Only rows without the mandatory values.
        $DB->insert_record(self::TABLE, ['userid' => 'userid1', 'courseid' => 'tc1', 'groupid' => '']);
        $plugin->sync_group_enrolments(new \null_progress_trace());
        $this->assertSame('enrol_database', $this->membership('c1grp1', 1));
        $this->assertSame('enrol_database', $this->membership('c1grp1', 2));

        // The synchronisation of a single user removes its memberships.
        $plugin->sync_user_group_enrolments($this->users[1]);
        $this->assertFalse($this->membership('c1grp1', 1));

        // With any valid row, the members that are not in the table are removed.
        $this->add_external('userid3', 'tc2', 'grp1');
        $plugin->sync_group_enrolments(new \null_progress_trace());
        $this->assertFalse($this->membership('c1grp1', 2));
        $this->assertSame('enrol_database', $this->membership('c2grp1', 3));

        $this->cleanup_enrol_database();
    }

    /**
     * Members added after the external data was read (e.g. by a login while the scheduled task runs) are not removed.
     */
    public function test_sync_group_enrolments_recent_members(): void {
        global $DB;
        $this->resetAfterTest();
        $this->preventResetByRollback();
        $this->init_enrol_database();
        $plugin = enrol_get_plugin('database');

        groups_add_member($this->groups['c1grp1']->id, $this->users[1]->id, 'enrol_database');
        groups_add_member($this->groups['c1grp1']->id, $this->users[2]->id, 'enrol_database');
        $DB->set_field('groups_members', 'timeadded', time() - 60, ['groupid' => $this->groups['c1grp1']->id]);
        $DB->set_field('groups_members', 'timeadded', time() + 60,
            ['groupid' => $this->groups['c1grp1']->id, 'userid' => $this->users[2]->id]);
        // Another membership, as nothing is removed if the external table is empty.
        $this->add_external('userid3', 'tc2', 'grp1');

        $plugin->sync_group_enrolments(new \null_progress_trace());
        $this->assertFalse($this->membership('c1grp1', 1));
        $this->assertSame('enrol_database', $this->membership('c1grp1', 2));

        $this->cleanup_enrol_database();
    }

    /**
     * Memberships are linked to the enrol_database instance of the course, so core removes them on unenrol.
     */
    public function test_sync_group_enrolments_itemid(): void {
        global $DB;
        $this->resetAfterTest();
        $this->preventResetByRollback();
        $this->init_enrol_database();
        $plugin = enrol_get_plugin('database');
        $trace = new \null_progress_trace();

        // Only course 1 has an enrol_database instance.
        $instanceid = $plugin->add_instance($this->courses[1]);
        $instance = $DB->get_record('enrol', ['id' => $instanceid]);
        $plugin->enrol_user($instance, $this->users[1]->id);

        $this->add_external('userid1', 'tc1', 'grp1');
        $this->add_external('userid1', 'tc2', 'grp1');
        $this->add_external('userid2', 'tc1', 'grp2');
        $plugin->sync_group_enrolments($trace);
        $itemid = fn(string $group, int $user) => (int) $DB->get_field('groups_members', 'itemid',
            ['groupid' => $this->groups[$group]->id, 'userid' => $this->users[$user]->id]);
        $this->assertEquals($instanceid, $itemid('c1grp1', 1));
        $this->assertEquals($instanceid, $itemid('c1grp2', 2));
        $this->assertEquals(0, $itemid('c2grp1', 1));

        // Memberships added with another itemid are linked to the instance in the next synchronisation.
        $DB->set_field('groups_members', 'itemid', 0, ['groupid' => $this->groups['c1grp2']->id]);
        $plugin->sync_group_enrolments($trace);
        $this->assertEquals($instanceid, $itemid('c1grp2', 2));

        // Unenrolling from the instance removes the memberships of the course, even if the user is still enrolled.
        $plugin->unenrol_user($instance, $this->users[1]->id);
        $this->assertTrue(is_enrolled(\context_course::instance($this->courses[1]->id), $this->users[1]->id));
        $this->assertFalse($this->membership('c1grp1', 1));
        $this->assertSame('enrol_database', $this->membership('c2grp1', 1));

        $this->cleanup_enrol_database();
    }

    /**
     * On login the user is first enrolled in the courses and then added to the groups, with the same connection.
     */
    public function test_sync_user_enrolments_with_groups(): void {
        global $CFG, $DB;
        $this->resetAfterTest();
        $this->preventResetByRollback();
        $this->init_enrol_database();
        $plugin = enrol_get_plugin('database');

        $dbman = $DB->get_manager();
        $table = new \xmldb_table('enrol_database_test_enrols');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_CHAR, '255', null, null, null);
        $table->add_field('courseid', XMLDB_TYPE_CHAR, '255', null, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        $dbman->create_table($table);
        $plugin->set_config('remoteenroltable', $CFG->prefix . 'enrol_database_test_enrols');
        $plugin->set_config('remoteuserfield', 'userid');
        $plugin->set_config('remotecoursefield', 'courseid');
        $plugin->set_config('remoterolefield', '');
        $plugin->set_config('defaultrole', $DB->get_field('role', 'id', ['shortname' => 'student']));

        // User 4 is not enrolled in course 1 yet.
        $DB->insert_record('enrol_database_test_enrols', ['userid' => 'userid4', 'courseid' => 'tc1']);
        $this->add_external('userid4', 'tc1', 'grp2');

        $plugin->sync_user_enrolments($this->users[4]);
        $instance = $DB->get_record('enrol', ['courseid' => $this->courses[1]->id, 'enrol' => 'database'], '*', MUST_EXIST);
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $instance->id, 'userid' => $this->users[4]->id]));
        $this->assertSame('enrol_database', $this->membership('c1grp2', 4));
        $this->assertEquals($instance->id, $DB->get_field('groups_members', 'itemid',
            ['groupid' => $this->groups['c1grp2']->id, 'userid' => $this->users[4]->id]));

        $dbman->drop_table($table);
        $this->cleanup_enrol_database();
    }

    /**
     * Courses and users can be matched with other fields.
     */
    public function test_sync_group_enrolments_mapping_fields(): void {
        $this->resetAfterTest();
        $this->preventResetByRollback();
        $this->init_enrol_database();
        $plugin = enrol_get_plugin('database');
        $plugin->set_config('localuserfield', 'username');
        $plugin->set_config('localcoursefield', 'id');

        $this->add_external('username1', (string) $this->courses[1]->id, 'grp1');
        $this->add_external('username2', (string) $this->courses[2]->id, 'grp1');

        $plugin->sync_group_enrolments(new \null_progress_trace());
        $this->assertSame('enrol_database', $this->membership('c1grp1', 1));
        $this->assertSame('enrol_database', $this->membership('c2grp1', 2));
        $this->assertFalse($this->membership('c2grp1', 1));

        $this->cleanup_enrol_database();
    }

    /**
     * An error synchronising the group memberships on login does not stop the login: the user is still enrolled
     * in the courses and the error is reported with debugging().
     */
    public function test_sync_user_enrolments_group_error(): void {
        global $CFG, $DB;
        $this->resetAfterTest();
        $this->preventResetByRollback();
        $this->init_enrol_database();

        $plugin = new class extends \enrol_database_plugin {
            protected function sync_group_memberships(\ADOConnection $extdb, \progress_trace $trace,
                    ?\stdClass $user = null): int {
                throw new \moodle_exception('error');
            }
        };
        $this->assertSame('database', $plugin->get_name());

        $dbman = $DB->get_manager();
        $table = new \xmldb_table('enrol_database_test_enrols');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_CHAR, '255', null, null, null);
        $table->add_field('courseid', XMLDB_TYPE_CHAR, '255', null, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        $dbman->create_table($table);
        $plugin->set_config('remoteenroltable', $CFG->prefix . 'enrol_database_test_enrols');
        $plugin->set_config('remoteuserfield', 'userid');
        $plugin->set_config('remotecoursefield', 'courseid');
        $plugin->set_config('remoterolefield', '');
        $plugin->set_config('defaultrole', $DB->get_field('role', 'id', ['shortname' => 'student']));
        $DB->insert_record('enrol_database_test_enrols', ['userid' => 'userid4', 'courseid' => 'tc1']);
        $this->add_external('userid4', 'tc1', 'grp2');
        // Empty the test error log, so that only the errors of this test are in it.
        file_put_contents(ini_get('error_log'), '');

        $plugin->sync_user_enrolments($this->users[4]);
        $this->assertDebuggingCalled();
        // The error is also written to the error log, which init_enrol_database() points to a test file.
        $this->assertStringContainsString(
            'enrol_database: error synchronising the group memberships of user ' . $this->users[4]->id,
            (string) file_get_contents(ini_get('error_log')));
        $instance = $DB->get_record('enrol', ['courseid' => $this->courses[1]->id, 'enrol' => 'database'], '*', MUST_EXIST);
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $instance->id, 'userid' => $this->users[4]->id]));
        $this->assertFalse($this->membership('c1grp2', 4));

        // The same when it is called with its own connection.
        $plugin->sync_user_group_enrolments($this->users[4]);
        $this->assertDebuggingCalled();

        $dbman->drop_table($table);
        $this->cleanup_enrol_database();
    }

    /**
     * The group enrolments are not synchronised without the course field, which is set in the 'newgroupcourse' setting.
     */
    public function test_sync_group_enrolments_without_course_field(): void {
        $this->resetAfterTest();
        $this->preventResetByRollback();
        $this->init_enrol_database();
        $plugin = enrol_get_plugin('database');

        groups_add_member($this->groups['c1grp1']->id, $this->users[1]->id, 'enrol_database');
        $this->age_members();
        $this->add_external('userid2', 'tc1', 'grp1');

        foreach (['', '  '] as $value) {
            $plugin->set_config('newgroupcourse', $value);
            $trace = new \progress_trace_buffer(new \text_progress_trace(), false);
            $this->assertSame(0, $plugin->sync_group_enrolments($trace));
            $this->assertStringContainsString('Group enrolments synchronisation skipped.', $trace->get_buffer());
            $plugin->sync_user_group_enrolments($this->users[2]);

            // Nothing changes: no members are added nor removed.
            $this->assertSame('enrol_database', $this->membership('c1grp1', 1));
            $this->assertFalse($this->membership('c1grp1', 2));
        }

        $this->cleanup_enrol_database();
    }

    /**
     * The remote field names may be configured in any case: the columns returned by the external database are
     * matched in lowercase, so no member is skipped nor removed because of the case of the column names.
     */
    public function test_sync_group_enrolments_field_names_case(): void {
        $this->resetAfterTest();
        $this->preventResetByRollback();
        $this->init_enrol_database();
        $plugin = enrol_get_plugin('database');
        $trace = new \null_progress_trace();
        $plugin->set_config('userfield', 'UserID');
        $plugin->set_config('newgroupcourse', 'CourseID');
        $plugin->set_config('groupfield', 'GroupID');

        // An existing member that is still in the external table must be kept.
        groups_add_member($this->groups['c1grp1']->id, $this->users[1]->id, 'enrol_database');
        $this->age_members();
        $this->add_external('userid1', 'tc1', 'grp1');
        $this->add_external('userid2', 'tc1', 'grp1');
        $this->add_external('userid3', 'tc2', 'grp1');

        $this->assertSame(0, $plugin->sync_group_enrolments($trace));
        $this->assertSame('enrol_database', $this->membership('c1grp1', 1));
        $this->assertSame('enrol_database', $this->membership('c1grp1', 2));
        $this->assertSame('enrol_database', $this->membership('c2grp1', 3));

        // The same applies to the synchronisation on login.
        $this->add_external('userid4', 'tc2', 'grp1');
        $plugin->sync_user_group_enrolments($this->users[4]);
        $this->assertSame('enrol_database', $this->membership('c2grp1', 4));

        $this->cleanup_enrol_database();
    }

    /**
     * The scheduled task creates a course and its group, enrols a user in the course and adds the user to the group, all
     * in the same run: the group memberships are synchronised after the enrolments.
     */
    public function test_task_sync_enrolments(): void {
        global $CFG, $DB;
        $this->resetAfterTest();
        $this->preventResetByRollback();
        $this->init_enrol_database();
        $plugin = enrol_get_plugin('database');

        // The plugin must be enabled for the task to run.
        $enabled = array_filter(explode(',', $CFG->enrol_plugins_enabled));
        set_config('enrol_plugins_enabled', implode(',', array_unique(array_merge($enabled, ['database']))));

        // External tables of new courses and of user enrolments.
        $dbman = $DB->get_manager();
        $tables = [
            'enrol_database_test_taskcourses' => ['fullname', 'shortname', 'idnumber'],
            'enrol_database_test_enrols' => ['userid', 'courseid'],
        ];
        foreach ($tables as $name => $fields) {
            $table = new \xmldb_table($name);
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            foreach ($fields as $field) {
                $table->add_field($field, XMLDB_TYPE_CHAR, '255', null, null, null);
            }
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            if ($dbman->table_exists($table)) {
                $dbman->drop_table($table);
            }
            $dbman->create_table($table);
        }

        $plugin->set_config('newcoursetable', $CFG->prefix . 'enrol_database_test_taskcourses');
        $plugin->set_config('newcoursefullname', 'fullname');
        $plugin->set_config('newcourseshortname', 'shortname');
        $plugin->set_config('newcourseidnumber', 'idnumber');
        $plugin->set_config('newcoursecategory', '');
        $plugin->set_config('templatecourse', '');
        $plugin->set_config('defaultcategory', \core_course_category::get_default()->id);
        $plugin->set_config('remoteenroltable', $CFG->prefix . 'enrol_database_test_enrols');
        $plugin->set_config('remoteuserfield', 'userid');
        $plugin->set_config('remotecoursefield', 'courseid');
        $plugin->set_config('remoterolefield', '');
        $plugin->set_config('defaultrole', $DB->get_field('role', 'id', ['shortname' => 'student']));
        $plugin->set_config('newgrouptable', $CFG->prefix . self::GROUPSTABLE);
        $plugin->set_config('newgroupname', 'name');
        $plugin->set_config('newgroupidnumber', 'idnumber');
        $plugin->set_config('groupmessaging', 0);

        // A new course, a new group in it, and user 1 enrolled in the course and added to the group.
        $DB->insert_record('enrol_database_test_taskcourses', ['fullname' => 'Task course', 'shortname' => 'tcnew',
            'idnumber' => 'tcnew']);
        $DB->insert_record(self::GROUPSTABLE, ['name' => 'New group', 'idnumber' => 'gnew', 'courseid' => 'tcnew']);
        $DB->insert_record('enrol_database_test_enrols', ['userid' => 'userid1', 'courseid' => 'tcnew']);
        $this->add_external('userid1', 'tcnew', 'gnew');

        $task = new \enrol_database\task\sync_enrolments();
        ob_start();
        $task->execute();
        $output = ob_get_clean();

        $course = $DB->get_record('course', ['shortname' => 'tcnew'], '*', MUST_EXIST);
        $group = $DB->get_record('groups', ['courseid' => $course->id, 'idnumber' => 'gnew'], '*', MUST_EXIST);
        $instance = $DB->get_record('enrol', ['courseid' => $course->id, 'enrol' => 'database'], '*', MUST_EXIST);
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $instance->id, 'userid' => $this->users[1]->id]));
        $this->assertEquals('enrol_database', $DB->get_field('groups_members', 'component',
            ['groupid' => $group->id, 'userid' => $this->users[1]->id]));
        $this->assertStringContainsString('Starting group enrolment synchronisation', $output);

        foreach (array_keys($tables) as $name) {
            $dbman->drop_table(new \xmldb_table($name));
        }
        $this->cleanup_enrol_database();
    }

    /**
     * The remote field names of the new groups table may be configured in any case.
     */
    public function test_sync_groups_field_names_case(): void {
        global $CFG, $DB;
        $this->resetAfterTest();
        $this->preventResetByRollback();
        $this->init_enrol_database();
        $plugin = new class extends \enrol_database_plugin {
            /** @var array[] fields of every external query. */
            public array $queries = [];

            protected function db_get_query(\ADOConnection $extdb, $table, array $conditions, array $fields,
                    $distinct = false, $sort = "") {
                $this->queries[] = $fields;
                return parent::db_get_query($extdb, $table, $conditions, $fields, $distinct, $sort);
            }
        };
        $this->assertSame('database', $plugin->get_name());
        $plugin->set_config('newgrouptable', $CFG->prefix . self::GROUPSTABLE);
        $plugin->set_config('newgroupname', 'Name');
        $plugin->set_config('newgroupidnumber', 'IDNumber');
        $plugin->set_config('newgroupcourse', 'CourseID');
        $plugin->set_config('newgroupdesc', 'Description');
        $plugin->set_config('groupmessaging', 0);

        $DB->insert_record(self::GROUPSTABLE, ['name' => 'Group A', 'idnumber' => 'ga', 'courseid' => 'tc1',
            'description' => 'Description A']);

        $this->assertSame(0, $plugin->sync_groups(new \null_progress_trace()));
        $group = $DB->get_record('groups', ['courseid' => $this->courses[1]->id, 'idnumber' => 'ga'], '*', MUST_EXIST);
        $this->assertEquals('Group A', $group->name);
        $this->assertEquals('Description A', $group->description);

        // The external query uses the names as configured, the case only matters when matching the results.
        $this->assertEquals([['Name', 'IDNumber', 'CourseID', 'Description']], $plugin->queries);

        $this->cleanup_enrol_database();
    }
}
