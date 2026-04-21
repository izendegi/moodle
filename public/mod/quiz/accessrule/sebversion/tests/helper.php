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

namespace quizaccess_sebversion;

use stdClass;
use phpunit_util;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/quiz/tests/quiz_question_helper_test_trait.php');

/**
 * Test helper class for the quizaccess_sebversion class.
 *
 * @package    quizaccess_sebversion
 * @copyright  2025, Philipp Imhof
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizaccess_sebversion_test_helper {
    use \quiz_question_helper_test_trait;

    /**
     * Create a test quiz in a given course with quizaccess_sebversion activated or not.
     *
     * @param stdClass $course
     * @param bool|null $enforceversion whether or not to enforce the SEB version, null for not set at all
     * @param bool $requireseb whether or not to require SEB for the quiz
     * @return stdClass
     */
    public static function create_test_quiz(stdClass $course, ?bool $enforceversion, bool $requireseb = true): stdClass {
        /** @var mod_quiz_generator $quizgenerator */
        $quizgenerator = phpunit_util::get_data_generator()->get_plugin_generator('mod_quiz');

        $quiz = $quizgenerator->create_instance([
            'course' => $course->id,
            'seb_requiresafeexambrowser' => $requireseb ? '1' : '0',
            'sebversion_enforce' => $enforceversion ? '1' : '0',
        ]);
        $quiz->coursemodule = $quiz->cmid;

        if ($enforceversion === null) {
            unset($quiz->sebversion_enforce);
        }

        return $quiz;
    }
}
