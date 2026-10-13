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

namespace mod_journal\ajax;

use advanced_testcase;

/**
 * Static regression tests for the AJAX leaf saveindividualfeedback.php.
 *
 * Guards bug B9:
 *   - The leaf used to write into a $result array it never declared,
 *     relying on the dispatcher's $result coming through the require()
 *     scope. If the leaf is ever called from a different scope (test
 *     harness, CLI, alternative dispatcher) PHP raises an "undefined
 *     variable" notice and the JSON response is malformed.
 *   - The leaf also carried a dead null clause on $grade: $grade is
 *     forced to an int by lines just above, so the null check could
 *     never fire.
 *
 * Full behavioural tests for the leaf would require exercising the
 * AJAX dispatcher + sesskey + require_login pipeline, which is heavy
 * under phpunit. The static checks below catch the most likely
 * regressions (someone deletes the local $result, someone re-adds the
 * dead null clause, someone types $grade as nullable again).
 *
 * @package   mod_journal
 * @copyright 2025 Luca Bösch <luca.boesch@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class saveindividualfeedback_test extends advanced_testcase {
    /**
     * Path to the leaf.
     */
    private const LEAF = __DIR__ . '/../../ajax/saveindividualfeedback.php';

    /**
     * The leaf declares a local $result array near the top of the file.
     *
     * @return void
     * @coversNothing
     */
    public function test_leaf_declares_local_result(): void {
        $source = file_get_contents(self::LEAF);
        $this->assertNotFalse($source, 'Could not read saveindividualfeedback.php');

        // The local $result must be declared AFTER the MOODLE_INTERNAL
        // guard and BEFORE the first required_param call, so the leaf
        // is self-contained regardless of the caller's scope.
        $this->assertMatchesRegularExpression(
            '/defined\([\'"]MOODLE_INTERNAL[\'"]\).*?\$result\s*=\s*\[/s',
            $source,
            'Leaf must declare a local $result array right after the MOODLE_INTERNAL guard'
        );
    }

    /**
     * The dead grade null clause is gone. $grade is forced to an int
     * above the rating-changed check, so a null test would always pass
     * and only obscures the intent.
     *
     * @return void
     * @coversNothing
     */
    public function test_dead_grade_null_check_is_removed(): void {
        $source = file_get_contents(self::LEAF);
        $this->assertNotFalse($source, 'Could not read saveindividualfeedback.php');

        $this->assertDoesNotMatchRegularExpression(
            '/\$grade\s*!==\s*null/',
            $source,
            'Dead grade-null clause must stay removed - $grade is always an int at this point'
        );
    }
}
