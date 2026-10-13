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

namespace mod_journal;

use advanced_testcase;
use coding_exception;
use dml_exception;

/**
 * Tests for the journal manager, specifically has_answered().
 *
 * Guards bug B4: the previous implementation called
 * \journal_get_completion_state($course, $cm, $userid, true). The
 * underlying helper only checks completion when $type === COMPLETION_AND,
 * so for any other value it just returned $type unchanged. That made
 * has_answered() return true for every student regardless of whether
 * they had actually written an entry, painting the "completed" tick on
 * the course-format overview in error.
 *
 * @package   mod_journal
 * @copyright 2025 Luca Bösch <luca.boesch@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \mod_journal\manager
 */
final class manager_test extends advanced_testcase {
    /**
     * A student with no entry has not answered.
     *
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @covers ::has_answered
     */
    public function test_has_answered_false_without_entry(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $journal = $this->getDataGenerator()->create_module('journal', ['course' => $course]);

        $this->setUser($student);

        $manager = manager::create_from_instance((object)['id' => $journal->id]);
        $this->assertFalse($manager->has_answered());
    }

    /**
     * A student with an entry has answered.
     *
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @covers ::has_answered
     */
    public function test_has_answered_true_with_entry(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $journal = $this->getDataGenerator()->create_module('journal', ['course' => $course]);

        $this->setUser($student);

        $this->getDataGenerator()->get_plugin_generator('mod_journal')->create_entry((object)[
            'journal' => $journal->id,
            'userid' => $student->id,
        ]);

        $manager = manager::create_from_instance((object)['id' => $journal->id]);
        $this->assertTrue($manager->has_answered());
    }

    /**
     * A second student's entry does not make the first student appear as having answered.
     *
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @covers ::has_answered
     */
    public function test_has_answered_is_user_specific(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $student1 = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $student2 = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $journal = $this->getDataGenerator()->create_module('journal', ['course' => $course]);

        // Only student2 has an entry.
        $this->getDataGenerator()->get_plugin_generator('mod_journal')->create_entry((object)[
            'journal' => $journal->id,
            'userid' => $student2->id,
        ]);

        $manager = manager::create_from_instance((object)['id' => $journal->id]);

        $this->setUser($student1);
        $this->assertFalse($manager->has_answered(), 'student1 must not be reported as having answered');

        $this->setUser($student2);
        $this->assertTrue($manager->has_answered(), 'student2 must be reported as having answered');
    }
}
