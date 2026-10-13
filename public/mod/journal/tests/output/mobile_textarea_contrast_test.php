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

namespace mod_journal\output;

use advanced_testcase;
use coding_exception;
use dml_exception;
use moodle_page;

/**
 * Regression tests for issue #161: dark-mode contrast on mobile-app textareas.
 *
 * The Moodle App renders journal activity views via the
 * {@see mobile} output class. In dark mode the OS inherits a dark body
 * background to <textarea>, so unless the template declares an explicit
 * background and color, the user's typed entry (and any feedback the
 * teacher types) becomes unreadable: dark text on dark background.
 *
 * The fix adds `background: white; color: #000; -webkit-appearance: none;`
 * to the inline style of both the student entry textarea
 * (mobile_edit_entry.mustache) and the teacher feedback textarea
 * (mobile_view.mustache). This test asserts the rendered HTML emitted by
 * the WS handler keeps those declarations.
 *
 * Driving the Ionic shell directly from phpunit is not feasible - the
 * shell lives in moodleapp-main and the dark-mode override is a CSS
 * variable resolved by Chromium at paint time. This test verifies the
 * plugin's own contract: the WS handler still emits the corrected style
 * attribute, so when the app renders it, the textarea has a known-good
 * background and colour.
 *
 * @package   mod_journal
 * @copyright 2025 Luca Bösch <luca.boesch@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \mod_journal\output\mobile
 */
final class mobile_textarea_contrast_test extends advanced_testcase {
    /**
     * Reset $PAGE and start a message sink before each test.
     *
     * The mobile_*() WS handlers we exercise here call require_login(),
     * which initialises the theme via $PAGE->initialise_theme_and_output().
     * Without a fresh $PAGE the theme leaks from one test into the next
     * and the second one fails with "The theme has already been set up
     * for this page".
     *
     * The message sink is started because enrolment in a course fires the
     * Moodle hooks system, which sends a welcome email through the email
     * message processor. On Moodle 4.4+ that processor renders the email
     * body via the Mustache renderer ($PAGE->get_renderer()), which also
     * initialises the theme. With the sink active, message_send() returns
     * the saved message id without ever calling the email processor, so
     * no renderer call and no theme initialisation happens. The sink is
     * automatically torn down by phpunit_util::reset_all_data() between
     * tests, so we do not need a matching tearDown().
     *
     * @return void
     */
    protected function setUp(): void {
        global $PAGE;
        parent::setUp();
        $PAGE = new moodle_page();
        $this->redirectMessages();
    }

    /**
     * Extract the inline style attribute of the textarea whose `name`
     * attribute equals $name in the rendered HTML.
     *
     * Returns the raw style string, or null if no such textarea exists.
     *
     * @param string $html Rendered template HTML.
     * @param string $name The textarea `name` attribute to find.
     * @return string|null
     */
    private static function extract_textarea_style(string $html, string $name): ?string {
        // Match the opening <textarea ...name="NAME"...> tag (non-greedy,
        // single tag only). We anchor on `name="..."` because the plugin
        // controls that attribute deterministically.
        $pattern = '/<textarea\b[^>]*\bname="' . preg_quote($name, '/') . '"[^>]*>/i';
        if (!preg_match($pattern, $html, $matches)) {
            return null;
        }
        $tag = $matches[0];
        if (!preg_match('/\bstyle\s*=\s*"([^"]*)"/i', $tag, $style)) {
            return null;
        }
        return $style[1];
    }

    /**
     * The student edit page renders the entry textarea with explicit
     * background and colour so it stays readable when the host app is
     * in dark mode.
     *
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @covers ::mobile_entry_edit
     */
    public function test_student_entry_textarea_has_dark_mode_contrast(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $journal = $this->getDataGenerator()->create_module('journal', ['course' => $course]);

        // SetUser() initialises an empty session and loads the user
        // record, which makes isloggedin() return true so that
        // require_login() inside the mobile WS handler does not redirect
        // to the login page. require_login() itself sets up the theme
        // and PAGE context, so we don't need to touch $PAGE here.
        $this->setUser($student);

        $result = mobile::mobile_entry_edit([
            'cmid' => $journal->cmid,
            'courseid' => $course->id,
        ]);

        $html = $result['templates'][0]['html'];

        $style = self::extract_textarea_style($html, 'text');
        $this->assertNotNull($style, 'student entry textarea must exist in the rendered edit page');

        // The fix is "background: white; color: #000; -webkit-appearance: none;"
        // (mirroring the pattern already used on the teacher grade <select> at
        // mobile_view.mustache:157). Accept any whitespace between tokens.
        $this->assertMatchesRegularExpression(
            '/\bbackground\s*:\s*white\b/i',
            $style,
            'student entry textarea must declare background:white to override dark-mode body inheritance'
        );
        $this->assertMatchesRegularExpression(
            '/\bcolor\s*:\s*[^;]+/i',
            $style,
            'student entry textarea must declare an explicit colour so typed text stays readable'
        );
        $this->assertMatchesRegularExpression(
            '/-webkit-appearance\s*:\s*none\b/i',
            $style,
            'student entry textarea must declare -webkit-appearance:none to suppress iOS native styling'
        );
    }

    /**
     * The teacher grading page renders the feedback textarea with the
     * same dark-mode fix.
     *
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @covers ::mobile_course_view
     */
    public function test_teacher_feedback_textarea_has_dark_mode_contrast(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $journal = $this->getDataGenerator()->create_module('journal', ['course' => $course]);

        // The teacher view only renders the feedback <textarea> for
        // submissions that exist. Add one for the student.
        $this->getDataGenerator()->get_plugin_generator('mod_journal')->create_entry((object)[
            'journal' => $journal->id,
            'userid' => $student->id,
            'text' => 'Student entry text.',
        ]);

        $this->setUser($teacher);

        $result = mobile::mobile_course_view([
            'cmid' => $journal->cmid,
            'courseid' => $course->id,
        ]);

        $html = $result['templates'][0]['html'];

        $style = self::extract_textarea_style($html, 'feedback');
        $this->assertNotNull($style, 'teacher feedback textarea must exist when at least one submission is present');

        $this->assertMatchesRegularExpression(
            '/\bbackground\s*:\s*white\b/i',
            $style,
            'teacher feedback textarea must declare background:white to override dark-mode body inheritance'
        );
        $this->assertMatchesRegularExpression(
            '/\bcolor\s*:\s*[^;]+/i',
            $style,
            'teacher feedback textarea must declare an explicit colour so typed text stays readable'
        );
        $this->assertMatchesRegularExpression(
            '/-webkit-appearance\s*:\s*none\b/i',
            $style,
            'teacher feedback textarea must declare -webkit-appearance:none to suppress iOS native styling'
        );
    }

    /**
     * Pre-existing behaviour guard: the student edit textarea was being
     * rendered before the fix landed. This test pins the textarea's
     * `name="text"` so a future template refactor that silently renames
     * it fails loudly here rather than in production.
     *
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @covers ::mobile_entry_edit
     */
    public function test_student_entry_textarea_keeps_name_attribute(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $journal = $this->getDataGenerator()->create_module('journal', ['course' => $course]);

        $this->setUser($student);

        $result = mobile::mobile_entry_edit([
            'cmid' => $journal->cmid,
            'courseid' => $course->id,
        ]);

        $this->assertStringContainsString(
            'name="text"',
            $result['templates'][0]['html'],
            'student entry textarea must keep name="text" so set_text WebService keeps matching it'
        );
    }
}
