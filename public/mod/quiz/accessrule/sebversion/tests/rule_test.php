<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Unit tests for the quizaccess_sebversion class.
 *
 * @package    quizaccess_sebversion
 * @copyright  2025, Philipp Imhof
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quizaccess_sebversion;

use mod_quiz\quiz_settings;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/quiz/accessrule/sebversion/tests/helper.php');

/**
 * Unit tests for the quizaccess_sebversion class.
 *
 * @copyright  2025, Philipp Imhof
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers     \quizaccess_sebversion
 */
final class rule_test extends \advanced_testcase {
    /**
     * Data provider.
     *
     * @return array
     */
    public static function provide_settings(): array {
        return [
            ['1', true],
            ['0', false],
            ['0', null],
        ];
    }

    /**
     * Make sure our settings are removed from the DB when a quiz is deleted.
     *
     * @param string $expected the expected value '1' or '0' according to the setting
     * @param ?bool $enforceversion whether or not to enforce the SEB version; null if no setting
     *
     * @dataProvider provide_settings
     */
    public function test_delete_settings(string $expected, ?bool $enforceversion): void {
        global $DB;

        // Login as admin user.
        $this->resetAfterTest(true);
        $this->setAdminUser();

        // Create a course and a quiz.
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $quiz = quizaccess_sebversion_test_helper::create_test_quiz($course, $enforceversion);
        $data = $DB->get_record('quizaccess_sebversion', ['quizid' => $quiz->id]);

        // The value must be in the DB now.
        self::assertEquals($expected, $data->enforceversion);

        // Delete the current course to make sure there is no data.
        delete_course($course, false);

        // Our setting must not be in the DB anymore.
        $data = $DB->get_record('quizaccess_sebversion', ['quizid' => $quiz->id]);
        self::assertEmpty($data);
    }

    /**
     * Make sure the plugin is activated if necessary, and not activated if not needed.
     *
     * @return void
     */
    public function test_plugin_activates_or_not(): void {
        global $DB;

        // Login as admin user.
        $this->resetAfterTest(true);
        $this->setAdminUser();

        // Create a course and a quiz.
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $quizwith = quizaccess_sebversion_test_helper::create_test_quiz($course, true);
        $quizwithout = quizaccess_sebversion_test_helper::create_test_quiz($course, false);
        $quiznoseb = quizaccess_sebversion_test_helper::create_test_quiz($course, true, false);
        $context = \context_course::instance($course->id);

        // For administrators, the plugin should not activate.
        $quizobj = quiz_settings::create($quizwith->id);
        $manager = $quizobj->get_access_manager(time());
        $rules = $manager->get_active_rule_names();
        self::assertNotContains('quizaccess_sebversion', $rules);

        // For teachers, it should not activate either.
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'teacher');
        $this->setUser($teacher);
        $quizobj = quiz_settings::create($quizwith->id);
        $manager = $quizobj->get_access_manager(time());
        $rules = $manager->get_active_rule_names();
        self::assertNotContains('quizaccess_sebversion', $rules);

        // For students, it should activate.
        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');
        $this->setUser($student);
        $quizobj = quiz_settings::create($quizwith->id);
        $manager = $quizobj->get_access_manager(time());
        $rules = $manager->get_active_rule_names();
        self::assertContains('quizaccess_sebversion', $rules);

        // Assign the student the quizaccess/seb:bypassseb capability. The plugin should then
        // no longer be activated.
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'student'], MUST_EXIST);
        assign_capability('quizaccess/seb:bypassseb', CAP_ALLOW, $roleid, $context->id);
        $quizobj = quiz_settings::create($quizwith->id);
        $manager = $quizobj->get_access_manager(time());
        $rules = $manager->get_active_rule_names();
        self::assertNotContains('quizaccess_sebversion', $rules);

        // Remove the capability and test that the student will now trigger our plugin again.
        unassign_capability('quizaccess/seb:bypassseb', $roleid, $context->id);
        $quizobj = quiz_settings::create($quizwith->id);
        $manager = $quizobj->get_access_manager(time());
        $rules = $manager->get_active_rule_names();
        self::assertContains('quizaccess_sebversion', $rules);

        // If the student attempts a quiz where the SEB version should not be enforced, the
        // plugin should not activate.
        $quizobj = quiz_settings::create($quizwithout->id);
        $manager = $quizobj->get_access_manager(time());
        $rules = $manager->get_active_rule_names();
        self::assertNotContains('quizaccess_sebversion', $rules);

        // If the student attempts a quiz where the SEB version should be enforced, but SEB
        // is not required, the plugin should not activate.
        $quizobj = quiz_settings::create($quiznoseb->id);
        $manager = $quizobj->get_access_manager(time());
        $rules = $manager->get_active_rule_names();
        self::assertNotContains('quizaccess_sebversion', $rules);
    }
}
