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

namespace mod_journal\external;

use advanced_testcase;
use coding_exception;
use dml_exception;
use invalid_parameter_exception;
use required_capability_exception;

/**
 * Unit tests for the class \mod_journal\external\get_entry.
 *
 * Guards bug B7: the previous code always cast $entry->rating to
 * (float), which converted a NULL rating to 0.0 - indistinguishable
 * from a real "0" grade. The fix returns the sentinel -1.0 when the
 * rating is NULL so the mobile app can still detect "no grade" the
 * same way it does for the no-entry branch.
 *
 * @runTestsInSeparateProcesses
 *
 * @package   mod_journal
 * @copyright 2025 Luca Bösch <luca.boesch@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \mod_journal\external\get_entry
 */
final class get_entry_rating_test extends advanced_testcase {
    /**
     * An ungraded entry returns rating = -1.0 (the "no grade" sentinel).
     *
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     * @covers ::execute
     */
    public function test_ungraded_entry_returns_minus_one_sentinel(): void {
        global $DB, $USER;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $journal = $this->getDataGenerator()->create_module('journal', ['course' => $course]);

        $DB->insert_record('journal_entries', (object)[
            'journal' => $journal->id,
            'userid' => $USER->id,
            'text' => 'student wrote this',
            'format' => FORMAT_PLAIN,
            'modified' => time(),
            'rating' => null,
            'entrycomment' => null,
            'teacher' => 0,
            'timemarked' => 0,
            'mailed' => 0,
        ]);

        $result = get_entry::execute($journal->cmid);

        $this->assertEquals(-1.0, (float) $result['rating']);
    }

    /**
     * A graded entry returns the actual rating, not the sentinel.
     *
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     * @covers ::execute
     */
    public function test_graded_entry_returns_real_rating(): void {
        global $DB, $USER;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $journal = $this->getDataGenerator()->create_module('journal', ['course' => $course]);

        $DB->insert_record('journal_entries', (object)[
            'journal' => $journal->id,
            'userid' => $USER->id,
            'text' => 'student wrote this',
            'format' => FORMAT_PLAIN,
            'modified' => time(),
            'rating' => 75,
            'entrycomment' => 'good',
            'teacher' => $USER->id,
            'timemarked' => time(),
            'mailed' => 0,
        ]);

        $result = get_entry::execute($journal->cmid);

        $this->assertEquals(75.0, (float) $result['rating']);
    }

    /**
     * A grade of 0 must not be confused with the -1.0 sentinel.
     *
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     * @covers ::execute
     */
    public function test_zero_grade_is_not_sentinel(): void {
        global $DB, $USER;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $journal = $this->getDataGenerator()->create_module('journal', ['course' => $course]);

        $DB->insert_record('journal_entries', (object)[
            'journal' => $journal->id,
            'userid' => $USER->id,
            'text' => 'student wrote this',
            'format' => FORMAT_PLAIN,
            'modified' => time(),
            'rating' => 0,
            'entrycomment' => 'see me',
            'teacher' => $USER->id,
            'timemarked' => time(),
            'mailed' => 0,
        ]);

        $result = get_entry::execute($journal->cmid);

        $this->assertEquals(0.0, (float) $result['rating']);
        $this->assertNotEquals(-1.0, (float) $result['rating']);
    }
}
