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

namespace core_backup;

use backup;
use backup_controller;
use restore_controller;
use restore_dbops;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

/**
 * Tests for the backup and restore of the component and itemid of groups.
 *
 * @package    core_backup
 * @copyright  2026 Moodle Pty Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\restore_groups_components_step::class)]
#[\PHPUnit\Framework\Attributes\CoversMethod(\restore_groups_structure_step::class, 'process_group')]
final class restore_groups_components_step_test extends \advanced_testcase {
    /**
     * Creates a course with groups created by different components.
     *
     * @return array The course, and the groups indexed by name.
     */
    protected function create_course_with_groups(): array {
        global $DB;

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $selfinstance = $DB->get_record('enrol', ['courseid' => $course->id, 'enrol' => 'self'], '*', MUST_EXIST);
        $manualinstance = $DB->get_record('enrol', ['courseid' => $course->id, 'enrol' => 'manual'], '*', MUST_EXIST);

        $groups = [
            'manual' => $generator->create_group(['courseid' => $course->id, 'name' => 'manual']),
            'self' => $generator->create_group([
                'courseid' => $course->id,
                'name' => 'self',
                'component' => 'enrol_self',
                'itemid' => $selfinstance->id,
            ]),
            'enrolmanual' => $generator->create_group([
                'courseid' => $course->id,
                'name' => 'enrolmanual',
                'component' => 'enrol_manual',
                'itemid' => $manualinstance->id,
            ]),
            // This component doesn't implement the restore_group callback.
            'forum' => $generator->create_group([
                'courseid' => $course->id,
                'name' => 'forum',
                'component' => 'mod_forum',
                'itemid' => 123,
            ]),
        ];

        return [$course, $groups];
    }

    /**
     * Backs up a course and restores it into a new course.
     *
     * @param \stdClass $course The course to back up.
     * @param int $enrolments The value of the enrolments restore setting (backup::ENROL_*).
     * @return int The id of the new course.
     */
    protected function backup_and_restore(\stdClass $course, int $enrolments): int {
        global $CFG, $USER;

        // Turn off file logging, otherwise it can't delete the file (Windows).
        $CFG->backup_file_logger_level = backup::LOG_NONE;

        $bc = new backup_controller(backup::TYPE_1COURSE, $course->id, backup::FORMAT_MOODLE, backup::INTERACTIVE_NO,
            backup::MODE_SAMESITE, $USER->id);
        $bc->get_plan()->get_setting('users')->set_value(false);
        $backupid = $bc->get_backupid();
        $backupbasepath = $bc->get_plan()->get_basepath();
        $bc->execute_plan();
        $results = $bc->get_results();
        $bc->destroy();

        // Unzip the backup if the temp directory doesn't contain the backup files.
        if (!file_exists($backupbasepath . '/moodle_backup.xml')) {
            $results['backup_destination']->extract_to_pathname(get_file_packer('application/vnd.moodle.backup'),
                $backupbasepath);
        }

        $newcourseid = restore_dbops::create_new_course($course->fullname . '_2', $course->shortname . '_2',
            $course->category);
        $rc = new restore_controller($backupid, $newcourseid, backup::INTERACTIVE_NO, backup::MODE_SAMESITE, $USER->id,
            backup::TARGET_NEW_COURSE);
        $rc->get_plan()->get_setting('users')->set_value(false);
        $rc->get_plan()->get_setting('enrolments')->set_value($enrolments);
        $this->assertTrue($rc->execute_precheck());
        $rc->execute_plan();
        $rc->destroy();

        return $newcourseid;
    }

    /**
     * Groups created by enrolment plugins are linked to the restored enrolment instances.
     */
    public function test_restore_with_enrolment_methods(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        [$course] = $this->create_course_with_groups();

        $newcourseid = $this->backup_and_restore($course, backup::ENROL_ALWAYS);

        $newgroups = $DB->get_records('groups', ['courseid' => $newcourseid], '', 'name, component, itemid');
        $this->assertCount(4, $newgroups);
        $newself = $DB->get_record('enrol', ['courseid' => $newcourseid, 'enrol' => 'self'], '*', MUST_EXIST);
        $newmanual = $DB->get_record('enrol', ['courseid' => $newcourseid, 'enrol' => 'manual'], '*', MUST_EXIST);

        $this->assertSame('', $newgroups['manual']->component);
        $this->assertEquals(0, $newgroups['manual']->itemid);
        $this->assertEquals('enrol_self', $newgroups['self']->component);
        $this->assertEquals($newself->id, $newgroups['self']->itemid);
        $this->assertEquals('enrol_manual', $newgroups['enrolmanual']->component);
        $this->assertEquals($newmanual->id, $newgroups['enrolmanual']->itemid);
        // Components without restore support are restored as standard groups.
        $this->assertSame('', $newgroups['forum']->component);
        $this->assertEquals(0, $newgroups['forum']->itemid);
    }

    /**
     * Groups created by enrolment plugins are restored as standard groups if the enrolment methods are not restored.
     */
    public function test_restore_without_enrolment_methods(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        [$course] = $this->create_course_with_groups();

        $newcourseid = $this->backup_and_restore($course, backup::ENROL_NEVER);

        $newgroups = $DB->get_records('groups', ['courseid' => $newcourseid], '', 'name, component, itemid');
        $this->assertCount(4, $newgroups);
        foreach ($newgroups as $group) {
            $this->assertSame('', $group->component, "Group {$group->name}");
            $this->assertEquals(0, $group->itemid, "Group {$group->name}");
        }
    }

    /**
     * The component and itemid of groups are included in the backup.
     */
    public function test_backup_includes_component_and_itemid(): void {
        global $CFG, $USER;

        $this->resetAfterTest();
        $this->setAdminUser();
        [$course, $groups] = $this->create_course_with_groups();
        $CFG->backup_file_logger_level = backup::LOG_NONE;

        // Import backups never include users: the setting is already disabled and locked.
        $bc = new backup_controller(backup::TYPE_1COURSE, $course->id, backup::FORMAT_MOODLE, backup::INTERACTIVE_NO,
            backup::MODE_IMPORT, $USER->id);
        $basepath = $bc->get_plan()->get_basepath();
        $bc->execute_plan();
        $bc->destroy();

        $xml = simplexml_load_file($basepath . '/groups.xml');
        $group = $xml->xpath('/groups/group[@id="' . $groups['self']->id . '"]')[0];
        $this->assertEquals('enrol_self', (string) $group->component);
        $this->assertEquals($groups['self']->itemid, (int) $group->itemid);
    }
}
