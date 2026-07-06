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
 * Class external_test
 *
 * @package     mod_typeform
 * @copyright   2026 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_typeform;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Class external_test
 *
 * This class contains unit tests for the external module, focusing on the
 * functionality provided by the external API methods. It ensures test cases
 * handle various scenarios, including success and error conditions, when adding
 * events using the Typeform module's external functions.
 *
 * @package     mod_typeform
 * @copyright   2026 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(mod_typeform_external::class)]
#[RunTestsInSeparateProcesses]
final class external_test extends \advanced_testcase {
    /**
     * test_typeform_add_event_returns_eventnotexists
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     */
    public function test_typeform_add_event_returns_eventnotexists(): void {
        global $CFG;

        $this->resetAfterTest(true);

        require_once($CFG->dirroot . '/mod/typeform/classes/external.php');

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $result = mod_typeform_external::add_event('asd', 0);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertFalse($result['success']);
        $this->assertEquals(get_string('eventnotexists', 'typeform'), $result['message']);
    }

    /**
     * test_typeform_add_event_returns_cmnotexists_1
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     */
    public function test_typeform_add_event_returns_cmnotexists_1(): void {
        global $CFG;

        $this->resetAfterTest(true);

        require_once($CFG->dirroot . '/mod/typeform/classes/external.php');

        // Create course.
        $course = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->create_module('typeform', [
                'course' => $course->id,
                'module' => 'typeform',
                'name' => 'Typeform 1,',
                'typeformid' => 'ft4ew3qd',
        ]);

        // Create student.
        $student = $this->getDataGenerator()->create_user();
        $this->setUser($student);
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');

        $result = mod_typeform_external::add_event('attempt_started', 0);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertFalse($result['success']);
        $this->assertEquals(get_string('cmnotexists', 'typeform'), $result['message']);
    }

    /**
     * test_typeform_add_event_returns_cmnotexists_2
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     */
    #[RunInSeparateProcess]
    public function test_typeform_add_event_returns_cmnotexists_2(): void {
        global $CFG;

        $this->resetAfterTest(true);

        require_once($CFG->dirroot . '/mod/typeform/classes/external.php');

        // Create course.
        $course = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->create_module('typeform', [
                'course' => $course->id,
                'module' => 'typeform',
                'name' => 'Typeform 1,',
                'typeformid' => 'ft4ew3qd',
        ]);

        // Create student.
        $student = $this->getDataGenerator()->create_user();
        $this->setUser($student);
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');

        $result = mod_typeform_external::add_event('attempt_started', 1000000000);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertFalse($result['success']);
        $this->assertEquals(get_string('cmnotexists', 'typeform'), $result['message']);
    }

    /**
     * test_typeform_add_event_returns_formstarted_ok
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     */
    #[RunInSeparateProcess]
    public function test_typeform_add_event_returns_formstarted_ok(): void {
        global $CFG;

        $this->resetAfterTest(true);

        require_once($CFG->dirroot . '/mod/typeform/classes/external.php');

        // Create course.
        $course = $this->getDataGenerator()->create_course();
        $typeformactivity = $this->getDataGenerator()->create_module('typeform', [
                'course' => $course->id,
                'module' => 'typeform',
                'name' => 'Typeform 1,',
                'typeformid' => 'ft4ew3qd',
        ]);

        $cm = get_coursemodule_from_instance('typeform', $typeformactivity->id, $course->id, false, MUST_EXIST);

        // Create student.
        $student = $this->getDataGenerator()->create_user();
        $this->setUser($student);
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');

        $result = mod_typeform_external::add_event('attempt_started', $cm->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertTrue($result['success']);
        $this->assertEquals(get_string('formstarted', 'typeform'), $result['message']);
    }

    /**
     * Tests the behavior of the add_event method in the Typeform module when attempting to add
     * an event that has already been logged.
     *
     * The test ensures that when an event of the same type is added multiple times for the
     * same course module, the method returns a response indicating that the event already exists.
     *
     * This includes validation of the result's structure and content, ensuring the correct
     * success status and localized message.
     *
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     */
    #[RunInSeparateProcess]
    public function test_typeform_add_event_returns_alreadyexist(): void {
        global $CFG;

        $this->resetAfterTest(true);

        require_once($CFG->dirroot . '/mod/typeform/classes/external.php');

        // Create course.
        $course = $this->getDataGenerator()->create_course();
        $typeformactivity = $this->getDataGenerator()->create_module('typeform', [
                'course' => $course->id,
                'module' => 'typeform',
                'name' => 'Typeform 1,',
                'typeformid' => 'ft4ew3qd',
        ]);

        $cm = get_coursemodule_from_instance('typeform', $typeformactivity->id, $course->id, false, MUST_EXIST);

        // Create student.
        $student = $this->getDataGenerator()->create_user();
        $this->setUser($student);
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');

        mod_typeform_external::add_event('attempt_started', $cm->id);
        $result = mod_typeform_external::add_event('attempt_started', $cm->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertTrue($result['success']);
        $this->assertEquals(get_string('alreadyexist', 'typeform'), $result['message']);
    }
}
