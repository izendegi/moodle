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
 * Unit tests for backup and restore of the quizaccess_sebversion plugin's data.
 *
 * @package    quizaccess_sebversion
 * @copyright  2025, Philipp Imhof
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quizaccess_sebversion;

use backup;
use backup_controller;
use restore_controller;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
require_once($CFG->dirroot . '/mod/quiz/accessrule/sebversion/tests/helper.php');

/**
 * Unit tests for backup and restore of the quizaccess_sebversion quiz setting.
 *
 * @copyright  2025, Philipp Imhof
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers     \restore_quizaccess_sebversion_subplugin
 * @covers     \backup_quizaccess_sebversion_subplugin
 * @covers     \quizaccess_sebversion
 */
final class backup_restore_test extends \advanced_testcase {
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
     * Backup and restore a quiz and check whether our setting has been conserved.
     *
     * @param string $expected the expected value '1' or '0' according to the setting
     * @param bool|null $enforceversion whether or not to enforce the SEB version; null if no setting
     *
     * @dataProvider provide_settings
     */
    public function test_backup_and_restore(string $expected, ?bool $enforceversion): void {
        global $DB, $USER;

        // Login as admin user.
        $this->resetAfterTest(true);
        $this->setAdminUser();

        // Create a course and a quiz.
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $quiz = quizaccess_sebversion_test_helper::create_test_quiz($course, $enforceversion);

        // Backup course. By using MODE_IMPORT, we avoid the backup being zipped.
        $bc = new backup_controller(
            backup::TYPE_1COURSE,
            $course->id,
            backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO,
            backup::MODE_IMPORT,
            $USER->id,
        );
        $backupid = $bc->get_backupid();
        $bc->execute_plan();
        $bc->destroy();

        // Delete the current course to make sure there is no data.
        delete_course($course, false);

        // Our setting must be in the quiz' XML file.
        $xmlfile = $bc->get_plan()->get_basepath() . "/activities/quiz_{$quiz->cmid}/quiz.xml";
        $xml = file_get_contents($xmlfile);
        $matches = [];
        preg_match(
            '#<quizaccess_sebversion>\s*<enforceversion>(1|0)</enforceversion>\s*</quizaccess_sebversion>#',
            $xml,
            $matches,
        );
        self::assertEquals($expected, $matches[1]);

        // Create a new course and restore the backup.
        $newcourse = $generator->create_course();
        $rc = new restore_controller(
            $backupid,
            $newcourse->id,
            backup::INTERACTIVE_NO,
            backup::MODE_IMPORT,
            $USER->id,
            backup::TARGET_NEW_COURSE,
        );
        $rc->execute_precheck();
        $rc->execute_plan();
        $rc->destroy();

        // Fetch the quiz ID.
        $modules = get_fast_modinfo($newcourse->id)->get_instances_of('quiz');
        $quiz = reset($modules);

        // Fetch the setting for the given quiz.
        $data = $DB->get_record('quizaccess_sebversion', ['quizid' => $quiz->instance]);
        self::assertEquals($expected, $data->enforceversion);
    }

    /**
     * Test that when restoring a quiz that was created before the installation of this plugin,
     * the setting will remain OFF, regardless of the default setting in the admin panel.
     */
    public function test_restoring_old_quiz(): void {
        global $DB, $USER;

        // Login as admin user.
        $this->resetAfterTest(true);
        $this->setAdminUser();

        // Set our default.
        set_config('enforcedefault', '1', 'quizaccess_sebversion');

        // Create a course and a quiz.
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $quiz = quizaccess_sebversion_test_helper::create_test_quiz($course, null);

        // Backup course. By using MODE_IMPORT, we avoid the backup being zipped.
        $bc = new backup_controller(
            backup::TYPE_1COURSE,
            $course->id,
            backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO,
            backup::MODE_IMPORT,
            $USER->id,
        );
        $backupid = $bc->get_backupid();
        $bc->execute_plan();
        $bc->destroy();

        // Delete our setting from the backup file.
        $xmlfile = $bc->get_plan()->get_basepath() . "/activities/quiz_{$quiz->cmid}/quiz.xml";
        $xml = file_get_contents($xmlfile);
        $xml = preg_replace(
            '#<quizaccess_sebversion>\s*<enforceversion>(1|0)</enforceversion>\s*</quizaccess_sebversion>#',
            '',
            $xml,
        );
        file_put_contents($xmlfile, $xml);

        // Delete the current course to make sure there is no data.
        delete_course($course, false);

        // Our setting must not be in the quiz' XML file anymore.
        $xml = file_get_contents($xmlfile);
        $matches = [];
        preg_match(
            '#<quizaccess_sebversion>\s*<enforceversion>(1|0)</enforceversion>\s*</quizaccess_sebversion>#',
            $xml,
            $matches,
        );
        self::assertEmpty($matches);

        // Create a new course and restore the backup.
        $newcourse = $generator->create_course();
        $rc = new restore_controller(
            $backupid,
            $newcourse->id,
            backup::INTERACTIVE_NO,
            backup::MODE_IMPORT,
            $USER->id,
            backup::TARGET_NEW_COURSE,
        );
        $rc->execute_precheck();
        $rc->execute_plan();
        $rc->destroy();

        // Fetch the quiz ID.
        $modules = get_fast_modinfo($newcourse->id)->get_instances_of('quiz');
        $quiz = reset($modules);

        // There should be no record in the DB for this quiz, because the setting has not been made.
        $data = $DB->get_record('quizaccess_sebversion', ['quizid' => $quiz->instance]);
        self::assertFalse($data);
    }
}
