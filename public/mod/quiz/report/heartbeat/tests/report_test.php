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

namespace quiz_heartbeat;

use Generator;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/quiz/tests/quiz_question_helper_test_trait.php');
require_once($CFG->dirroot . '/mod/quiz/report/heartbeat/report.php');
require_once($CFG->dirroot . '/mod/quiz/report/heartbeat/tests/helper.php');
require_once($CFG->dirroot . '/mod/quiz/report/reportlib.php');

/**
 * Tests for heartbeat quiz report plugin (quiz_heartbeat)
 *
 * @package   quiz_heartbeat
 * @copyright 2024 Philipp E. Imhof
 * @author    Philipp E. Imhof
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \quiz_heartbeat_report
 */
final class report_test extends \advanced_testcase {
    use \quiz_question_helper_test_trait;

    public function test_lot_of_students(): void {
        $this->resetAfterTest();

        // Create a course and a quiz with two regular questions.
        $generator = $this->getDataGenerator();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $course = $generator->create_course();
        $quiz = $this->create_test_quiz($course);
        $this->add_two_regular_questions($questiongenerator, $quiz);

        // Add 500 students and attempts.
        $nbofstudents = 500;
        $responsetime = time();
        for ($i = 0; $i < $nbofstudents; $i++) {
            $student = \phpunit_util::get_data_generator()->create_user();
            \phpunit_util::get_data_generator()->enrol_user($student->id, $course->id, 'student');
            $attempt = quiz_heartbeat_test_helper::start_attempt_at_quiz($quiz, $student);
            $this->setUser($student);
            $tosubmit = [
                1 => ['answer' => 'Here we go.'],
                2 => ['answer' => $i],
            ];
            $attempt[2]->process_submitted_actions($responsetime + $i, false, $tosubmit);
        }

        $fetchedattempts = quiz_heartbeat_test_helper::fetch_attempts($quiz, $course);
        self::assertCount($nbofstudents, $fetchedattempts);
    }

    public function test_only_inprogress_attempts_are_fetched(): void {
        $this->resetAfterTest();

        // Create a course and a quiz with two regular questions.
        $generator = $this->getDataGenerator();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $course = $generator->create_course();
        $quiz = $this->create_test_quiz($course);
        $this->add_two_regular_questions($questiongenerator, $quiz);

        // Add some students and attempts.
        $students = quiz_heartbeat_test_helper::add_students($course);
        foreach ($students as $i => $student) {
            // Return will be array containing $quizobj, $quba, $attemptobj.
            $attempts[$i] = quiz_heartbeat_test_helper::start_attempt_at_quiz($quiz, $student);
        }

        $fetchedattempts = quiz_heartbeat_test_helper::fetch_attempts($quiz, $course);

        // The query should return the most recent attempt step for every student, so the counts must match.
        self::assertCount(count($students), $fetchedattempts);

        // Now add a response for the first student.
        $this->setUser($students[0]);
        $responsetime = time() + 4;
        $tosubmit = [
            1 => ['answer' => 'Here we go.'],
            2 => ['answer' => '1'],
        ];
        // Reminder: the array $attempts contains the $quizobj, $quba and $attemptobj.
        $attempts[0][2]->process_submitted_actions($responsetime, false, $tosubmit);

        // Fetch the attemps again, the count should still match.
        $fetchedattempts = quiz_heartbeat_test_helper::fetch_attempts($quiz, $course);
        self::assertCount(count($students), $fetchedattempts);

        // Now, finish the first student's attempt, refetch and make sure one attempt is gone.
        // Note: The if/else can be removed as soon as 5.0 is the lowest supported version.
        if (method_exists($attempts[0][2], 'process_submit')) {
            $attempts[0][2]->process_submit($responsetime, false);
        } else {
            $attempts[0][2]->process_finish($responsetime, false);
        }
        $fetchedattempts = quiz_heartbeat_test_helper::fetch_attempts($quiz, $course);
        self::assertCount(count($students) - 1, $fetchedattempts);
    }

    public function test_user_has_open_and_finished_attempt(): void {
        $this->resetAfterTest();

        // Create a course and a quiz with two regular questions.
        $generator = $this->getDataGenerator();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $course = $generator->create_course();
        $quiz = $this->create_test_quiz($course);
        $this->add_two_regular_questions($questiongenerator, $quiz);

        // Add students, start attempts at two quizzes.
        $students = quiz_heartbeat_test_helper::add_students($course);
        $student = reset($students);
        $quizzes = [
            $this->create_test_quiz($course),
            $this->create_test_quiz($course),
        ];
        $attempts = [];
        foreach ($quizzes as $i => $quiz) {
            $this->add_two_regular_questions($questiongenerator, $quiz);
            $attempts[$i] = quiz_heartbeat_test_helper::start_attempt_at_quiz($quiz, $student);
        }

        // Submit response to both quizzes.
        $this->setUser($student);
        $responsetime = time() + 4;
        $tosubmit = [
            1 => ['answer' => 'Here we go.'],
            2 => ['answer' => '1'],
        ];
        foreach ($quizzes as $i => $quiz) {
            $attempts[$i][2]->process_submitted_actions($responsetime, false, $tosubmit);
        }

        // Finish second quiz.
        $finishtime = time() + 10;
        // Note: The if/else can be removed as soon as 5.0 is the lowest supported version.
        if (method_exists($attempts[1][2], 'process_submit')) {
            $attempts[1][2]->process_submit($finishtime, false);
        } else {
            $attempts[1][2]->process_finish($finishtime, false);
        }

        // Fetch attempts for first quiz. There must be only one and the timestamp must match
        // the response time.
        $fetchedattempts = quiz_heartbeat_test_helper::fetch_attempts($quizzes[0], $course);
        $fetchedattempt = reset($fetchedattempts);
        self::assertCount(1, $fetchedattempts);
        self::assertEquals($responsetime, $fetchedattempt->timecreated);
    }

    public function test_start_attempt_and_save_once_without_groups(): void {
        $this->resetAfterTest();

        // Create a course and a quiz with two regular questions.
        $generator = $this->getDataGenerator();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $course = $generator->create_course();
        $quiz = $this->create_test_quiz($course);
        $this->add_two_regular_questions($questiongenerator, $quiz);

        // Add some students and attempts.
        $students = quiz_heartbeat_test_helper::add_students($course);
        $starttime = time();
        foreach ($students as $i => $student) {
            // Return will be array containing $quizobj, $quba, $attemptobj.
            $attempts[$i] = quiz_heartbeat_test_helper::start_attempt_at_quiz($quiz, $student);
        }

        $fetchedattempts = quiz_heartbeat_test_helper::fetch_attempts($quiz, $course);

        $i = 0;
        foreach ($fetchedattempts as $fetcheddata) {
            // Check the name and firstname. Students are sorted by lastname, which is also the default
            // for our query.
            self::assertEquals($students[$i]->firstname, $fetcheddata->firstname);
            self::assertEquals($students[$i]->lastname, $fetcheddata->lastname);

            // The sequence number should be 0, because we have just started the attempt.
            self::assertEquals(0, $fetcheddata->sequencenumber);

            // The timestamp should be not more than a few seconds off from the registered time above.
            self::assertLessThanOrEqual(2, abs($fetcheddata->timecreated - $starttime));
            $i++;
        }

        // Now add a response for the first student.
        $this->setUser($students[0]);
        $responsetime = time() + 4;
        $tosubmit = [
            1 => ['answer' => 'Here we go.'],
            2 => ['answer' => '1'],
        ];
        // Reminder: the array $attempts contains the $quizobj, $quba and $attemptobj.
        $attempts[0][2]->process_submitted_actions($responsetime, false, $tosubmit);

        // Fetch the attemps again using the report's API.
        $fetchedattempts = quiz_heartbeat_test_helper::fetch_attempts($quiz, $course);
        $firststudentsattempt = reset($fetchedattempts);

        // The attempt step should now have a different time and sequence number.
        self::assertEquals(1, $firststudentsattempt->sequencenumber);
        self::assertLessThanOrEqual(2, abs($firststudentsattempt->timecreated - $responsetime));
    }

    public function test_fetching_attempts_with_separated_groups(): void {
        $this->resetAfterTest();

        // Create a course and a quiz with two questions. The quiz is configured to have
        // separate groups.
        $generator = $this->getDataGenerator();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $course = $generator->create_course();
        $quizgenerator = $generator->get_plugin_generator('mod_quiz');
        $quiz = $quizgenerator->create_instance(['course' => $course->id, 'sumgrades' => 2, 'groupmode' => SEPARATEGROUPS]);
        $this->add_two_regular_questions($questiongenerator, $quiz);

        // Add some students and attempts.
        $students = quiz_heartbeat_test_helper::add_students($course);
        foreach ($students as $i => $student) {
            // Return will be array containing $quizobj, $quba, $attemptobj.
            $attempts[$i] = quiz_heartbeat_test_helper::start_attempt_at_quiz($quiz, $student);
        }

        // Add the students to different groups. Taking the second student for group 1 and the
        // others for group 2.
        $group1 = $generator->create_group(['courseid' => $course->id]);
        $group2 = $generator->create_group(['courseid' => $course->id]);
        $generator->create_group_member(['groupid' => $group1->id, 'userid' => $students[1]->id]);
        for ($i = 0; $i < count($students); $i++) {
            if ($i == 1) {
                continue;
            }
            $generator->create_group_member(['groupid' => $group2->id, 'userid' => $students[$i]->id]);
        }

        $fetchedattempts = quiz_heartbeat_test_helper::fetch_attempts($quiz, $course);

        // The first group is automatically active and has only one student.
        self::assertCount(1, $fetchedattempts);

        // Comparing to the second student; the students are sorted by lastname and this is also
        // the default sorting for the DB query.
        $attemptofsecondstudent = reset($fetchedattempts);
        self::assertEquals($students[1]->firstname, $attemptofsecondstudent->firstname);
        self::assertEquals($students[1]->lastname, $attemptofsecondstudent->lastname);

        // Now, add one more student to group 1 and refetch. We'll just check the count.
        $generator->create_group_member(['groupid' => $group1->id, 'userid' => $students[0]->id]);
        $fetchedattempts = quiz_heartbeat_test_helper::fetch_attempts($quiz, $course);
        self::assertCount(2, $fetchedattempts);

        // Finally, adding a (non-editing) teacher to group 2 with the three students. After
        // re-initialisation of the report, we should now get 3 attempts.
        $teacher = $generator->create_user();
        $generator->enrol_user($teacher->id, $course->id, 'teacher');
        $generator->create_group_member(['groupid' => $group2->id, 'userid' => $teacher->id]);
        $this->setUser($teacher);
        $fetchedattempts = quiz_heartbeat_test_helper::fetch_attempts($quiz, $course);
        self::assertCount(3, $fetchedattempts);
    }

    public function test_only_attempts_for_given_quiz_are_fetched(): void {
        $this->resetAfterTest();

        // Create a course and two quizzes with two regular questions each.
        $generator = $this->getDataGenerator();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $course = $generator->create_course();
        $quizzes = [
            $this->create_test_quiz($course),
            $this->create_test_quiz($course),
        ];
        foreach ($quizzes as $quiz) {
            $this->add_two_regular_questions($questiongenerator, $quiz);
        }

        // Add some students and attempts; first three students take quiz 1, last takes quiz 2.
        $students = quiz_heartbeat_test_helper::add_students($course);
        foreach ($students as $i => $student) {
            // Return will be array containing $quizobj, $quba, $attemptobj.
            $attempts[$i] = quiz_heartbeat_test_helper::start_attempt_at_quiz($quizzes[$i < 3 ? 0 : 1], $student);
        }

        // Now add a response for all students, making sure they all have the same timestamp.
        $responsetime = time() + 4;
        foreach ($students as $i => $student) {
            $this->setUser($student);
            $tosubmit = [
                1 => ['answer' => 'Here we go.'],
                2 => ['answer' => '1'],
            ];

            // Reminder: the array $attempts contains the $quizobj, $quba and $attemptobj.
            $attempts[$i][2]->process_submitted_actions($responsetime, false, $tosubmit);
        }

        $fetchedattempts = quiz_heartbeat_test_helper::fetch_attempts($quizzes[0], $course);

        // There should be 3 attempts registered.
        self::assertCount(3, $fetchedattempts);

        // Fetching everything for the other quiz, we should have 1 attempt.
        $fetchedattempts = quiz_heartbeat_test_helper::fetch_attempts($quizzes[1], $course);
        self::assertCount(1, $fetchedattempts);

        // Checking the details for the attempt: sequencenumber must be 1 (one submitted answer),
        // name must match the last student.
        $attempt = reset($fetchedattempts);
        self::assertEquals(1, $attempt->sequencenumber);
        self::assertEquals($responsetime, $attempt->timecreated);
        $student = end($students);
        self::assertEquals($student->firstname, $attempt->firstname);
        self::assertEquals($student->lastname, $attempt->lastname);

        // Have the first student start an attempt at the other quiz as well and submit an answer
        // with a higher timestamp.
        $student = reset($students);
        $attempt = quiz_heartbeat_test_helper::start_attempt_at_quiz($quizzes[1], $student);
        $laterresponsetime = time() + 10;
        $this->setUser($student);
        $tosubmit = [
            1 => ['answer' => 'Here we go again.'],
            2 => ['answer' => '2'],
        ];
        $attempt[2]->process_submitted_actions($laterresponsetime, false, $tosubmit);

        $fetchedattempts = quiz_heartbeat_test_helper::fetch_attempts($quizzes[0], $course);

        // The timestamp should not have changed.
        foreach ($fetchedattempts as $fetchedattempt) {
            self::assertEquals($responsetime, $fetchedattempt->timecreated);
        }

        $fetchedattempts = quiz_heartbeat_test_helper::fetch_attempts($quizzes[1], $course);
        self::assertEquals($laterresponsetime, reset($fetchedattempts)->timecreated);
    }

    /**
     * Provide data to test sorting.
     *
     * @return Generator
     */
    public static function provide_sort_orders(): Generator {
        yield [
            'MüllerMarazzoDupontDoe',
            ['field' => 'lastname', 'order' => SORT_DESC],
        ];
        yield [
            'DoeDupontMarazzoMüller',
            ['field' => 'lastname', 'order' => SORT_ASC],
        ];
        yield [
            'MarazzoDoeDupontMüller',
            ['field' => 'firstname', 'order' => SORT_DESC],
        ];
        yield [
            'MüllerDupontDoeMarazzo',
            ['field' => 'firstname', 'order' => SORT_ASC],
        ];
        yield [
            'DupontMüllerDoeMarazzo',
            ['field' => 'time', 'order' => SORT_DESC],
        ];
        yield [
            'MarazzoDoeMüllerDupont',
            ['field' => 'time', 'order' => SORT_ASC],
        ];
    }

    /**
     * Test sorting by name or time elapsed.
     *
     * @dataProvider provide_sort_orders
     *
     * @param string $expected expected order of names
     * @param array $criteria which column (firstname, lastname, time) to sort and what way (ASC/DESC)
     * @return void
     */
    public function test_sorting($expected, $criteria): void {
        $this->resetAfterTest();

        // Create a course and a quiz with two regular questions.
        $generator = $this->getDataGenerator();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $course = $generator->create_course();
        $quiz = $this->create_test_quiz($course);
        $this->add_two_regular_questions($questiongenerator, $quiz);

        // Add some students and start attempts.
        $students = quiz_heartbeat_test_helper::add_students($course);
        foreach ($students as $i => $student) {
            $this->setUser($student);
            // Return will be array containing $quizobj, $quba, $attemptobj.
            $attempts[$i] = quiz_heartbeat_test_helper::start_attempt_at_quiz($quiz, $student);
        }

        // Post answers in other order, because we don't want time based sorting to be the same as
        // name based.
        foreach ([1, 3, 0, 2] as $studentnumber) {
            // Only sleep if sorting by time.
            if ($criteria['field'] === 'time') {
                sleep(1);
            }
            $tosubmit = [
                1 => ['answer' => 'Here we go.'],
                2 => ['answer' => "$studentnumber"],
            ];
            $this->setUser($students[$studentnumber]);
            $attempts[$studentnumber][2]->process_submitted_actions(time(), false, $tosubmit);
        }

        $cm = get_coursemodule_from_id('quiz', $quiz->cmid);
        $report = new local\heartbeat_report();
        [$currentgroup, $allstudentjoins, $groupstudentjoins, $allowedjoins] =
            $report->init('heartbeat', 'quiz_heartbeat\form\heartbeat_form', $quiz, $cm, $course);

        // Use reflection to force shortening of names.
        $reflectedreport = new \ReflectionClass($report);
        $reflectedoptions = $reflectedreport->getProperty('options');
        $reflectedoptions->setAccessible(true);
        $options = new local\heartbeat_options('heartbeat', $quiz, $cm, $course);
        $options->tsort = $criteria['field'];
        $options->tdir = $criteria['order'];
        $reflectedoptions->setValue($report, $options);

        // Fetch the attemps using the report's API.
        $fetchedattempts = $report->get_pending_attempts($groupstudentjoins);
        $sorting = array_reduce($fetchedattempts, function ($carry, $item) {
            return $carry .= $item->lastname;
        }, '');
        self::assertEquals($expected, $sorting);
    }

    /**
     * Provide data to test filtering by first or last name.
     *
     * @return Generator
     */
    public static function provide_filter_conditions(): Generator {
        yield [
            'MarazzoMüller',
            ['field' => 'tilast', 'initial' => 'M'],
        ];
        yield [
            'DoeDupont',
            ['field' => 'tilast', 'initial' => 'D'],
        ];
        yield [
            'MarazzoMüller',
            ['field' => 'tilast', 'initial' => 'm'],
        ];
        yield [
            'DoeDupont',
            ['field' => 'tilast', 'initial' => 'd'],
        ];
        yield [
            '',
            ['field' => 'tilast', 'initial' => 'x'],
        ];
        yield [
            'DoeDupont',
            ['field' => 'tifirst', 'initial' => 'J'],
        ];
        yield [
            'Marazzo',
            ['field' => 'tifirst', 'initial' => 'P'],
        ];
        yield [
            'Müller',
            ['field' => 'tifirst', 'initial' => 'G'],
        ];
        yield [
            'DoeDupont',
            ['field' => 'tifirst', 'initial' => 'j'],
        ];
        yield [
            'Marazzo',
            ['field' => 'tifirst', 'initial' => 'p'],
        ];
        yield [
            'Müller',
            ['field' => 'tifirst', 'initial' => 'g'],
        ];
        yield [
            '',
            ['field' => 'tifirst', 'initial' => 'z'],
        ];
    }

    /**
     * Test filtering by initial of first or last name.
     *
     * @dataProvider provide_filter_conditions
     *
     * @param string $expected expected order of names
     * @param array $criteria which field (tifirst, tilast) to filter and what letter to use
     * @return void
     */
    public function test_filtering($expected, $criteria): void {
        $this->resetAfterTest();

        // Create a course and a quiz with two regular questions.
        $generator = $this->getDataGenerator();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $course = $generator->create_course();
        $quiz = $this->create_test_quiz($course);
        $this->add_two_regular_questions($questiongenerator, $quiz);

        // Add some students and start attempts.
        $students = quiz_heartbeat_test_helper::add_students($course);
        foreach ($students as $i => $student) {
            $this->setUser($student);
            // Return will be array containing $quizobj, $quba, $attemptobj.
            $attempts[$i] = quiz_heartbeat_test_helper::start_attempt_at_quiz($quiz, $student);
            $tosubmit = [
                1 => ['answer' => 'Here we go.'],
                2 => ['answer' => 1],
            ];
            $this->setUser($student);
            $attempts[$i][2]->process_submitted_actions(time(), false, $tosubmit);
        }

        $cm = get_coursemodule_from_id('quiz', $quiz->cmid);
        $report = new local\heartbeat_report();
        [$currentgroup, $allstudentjoins, $groupstudentjoins, $allowedjoins] =
            $report->init('heartbeat', 'quiz_heartbeat\form\heartbeat_form', $quiz, $cm, $course);

        // Use reflection to force shortening of names.
        $reflectedreport = new \ReflectionClass($report);
        $reflectedoptions = $reflectedreport->getProperty('options');
        $reflectedoptions->setAccessible(true);
        $options = new local\heartbeat_options('heartbeat', $quiz, $cm, $course);
        $options->{$criteria['field']} = $criteria['initial'];
        $reflectedoptions->setValue($report, $options);

        // Fetch the attemps using the report's API.
        $fetchedattempts = $report->get_pending_attempts($groupstudentjoins);
        $sorting = array_reduce($fetchedattempts, function ($carry, $item) {
            return $carry .= $item->lastname;
        }, '');
        self::assertEquals($expected, $sorting);
    }
}
