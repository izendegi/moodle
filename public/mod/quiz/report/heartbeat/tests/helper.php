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
 * Helper for heartbeat quiz report tests (quiz_heartbeat)
 *
 * @package   quiz_heartbeat
 * @copyright 2024 Philipp E. Imhof
 * @author    Philipp E. Imhof
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quiz_heartbeat;

use advanced_testcase;
use stdClass;

/**
 * Helper class providing some useful methods for Essay responses downloader plugin unit
 * tests (quiz_heartbeat).
 *
 * @package   quiz_heartbeat
 * @copyright 2024 Philipp E. Imhof
 * @author    Philipp E. Imhof
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_heartbeat_test_helper {
    /**
     * Helper method to add a few students to a course.
     *
     * @param stdclass $course
     * @return stdclass[] the generated students
     */
    public static function add_students(stdclass $course): array {
        $names = [
            ['firstname' => 'John', 'lastname' => 'Doe'],
            ['firstname' => 'Jean', 'lastname' => 'Dupont'],
            ['firstname' => 'Pietro', 'lastname' => 'Marazzo'],
            ['firstname' => 'Georg', 'lastname' => 'Müller'],
        ];
        $students = [];
        foreach ($names as $i => $name) {
            $student = \phpunit_util::get_data_generator()->create_user($name);
            \phpunit_util::get_data_generator()->enrol_user($student->id, $course->id, 'student');
            $students[] = $student;
        }
        return $students;
    }

    /**
     * Start an attempt at a quiz for a user.
     *
     * @param stdclass $quiz Quiz to attempt.
     * @param stdclass $user A user to attempt the quiz.
     * @param int $attemptnumber
     * @return array
     */
    public static function start_attempt_at_quiz(stdclass $quiz, stdclass $user, $attemptnumber = 1): array {
        advanced_testcase::setUser($user);

        $starttime = time();
        $quizobj = \mod_quiz\quiz_settings::create($quiz->id, $user->id);

        $quba = \question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);

        // Start the attempt.
        $attempt = quiz_create_attempt($quizobj, $attemptnumber, null, $starttime, false, $user->id);
        quiz_start_new_attempt($quizobj, $quba, $attempt, $attemptnumber, $starttime);
        quiz_attempt_save_started($quizobj, $quba, $attempt);
        $attemptobj = \mod_quiz\quiz_attempt::create($attempt->id);

        // Render each question. This is needed, because starting with Moodle 5.0, the timecreated field
        // of the first question_attempt_step is not automatically set when the attempt is created, but
        // rather when the question is rendered for the first time.
        $displayoptions = new \question_display_options();
        $slots = $attemptobj->get_slots();
        foreach ($slots as $slot) {
            $attemptobj->get_question_attempt($slot)->render($displayoptions, null);
        }

        advanced_testcase::setUser();

        return [$quizobj, $quba, $attemptobj];
    }

    /**
     * Prepare and initialize a quiz_heartbeat report and fetch the attempts for a given quiz in
     * a given course while taking into account the group settings.
     *
     * @param stdClass $quiz the quiz for which the attempts should be fetched
     * @param stdClass $course the course containing the quiz, created e.g. by create_course()
     * @return array
     */
    public static function fetch_attempts(stdClass $quiz, stdClass $course): array {
        $cm = get_coursemodule_from_id('quiz', $quiz->cmid);
        $report = new local\heartbeat_report();
        [$currentgroup, $allstudentjoins, $groupstudentjoins, $allowedjoins] =
            $report->init('heartbeat', 'quiz_heartbeat\form\heartbeat_form', $quiz, $cm, $course);

        return $report->get_pending_attempts($groupstudentjoins);
    }
}
