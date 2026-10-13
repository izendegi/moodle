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
 * Tests for the AJAX dispatcher mod/journal/ajax/ajax.php.
 *
 * Guards bug B8: the dispatcher used to load any *.php file under
 * ajax/ as long as it existed on disk and the action parameter was
 * alphabetic. The fix introduces an explicit allow-list. This test
 * verifies the dispatcher:
 *   1. Declares the allow-list and checks it before requiring any leaf.
 *   2. Names every currently-shipped action in the allow-list.
 *
 * A full behavioural test of the dispatcher would require invoking
 * AJAX_SCRIPT + require_login + header() in-process, which is fragile
 * under phpunit. The static checks below catch the common regressions
 * (allow-list removed, action file renamed, new action added without
 * being whitelisted) without that overhead.
 *
 * @package   mod_journal
 * @copyright 2025 Luca Bösch <luca.boesch@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class ajax_dispatcher_test extends advanced_testcase {
    /**
     * Path to the dispatcher.
     */
    private const DISPATCHER = __DIR__ . '/../../ajax/ajax.php';

    /**
     * The dispatcher declares an $allowedactions array.
     *
     * @return void
     * @coversNothing
     */
    public function test_dispatcher_declares_allow_list(): void {
        $source = file_get_contents(self::DISPATCHER);
        $this->assertNotFalse($source, 'Could not read ajax.php');

        $this->assertMatchesRegularExpression(
            '/\$allowedactions\s*=\s*\[/',
            $source,
            'Dispatcher must declare an $allowedactions array'
        );
    }

    /**
     * The dispatcher rejects actions not in the allow-list before
     * touching the filesystem.
     *
     * @return void
     * @coversNothing
     */
    public function test_dispatcher_rejects_unknown_actions_before_file_exists(): void {
        $source = file_get_contents(self::DISPATCHER);
        $this->assertNotFalse($source, 'Could not read ajax.php');

        // Position of the allow-list guard vs. the file_exists() call.
        $guardpos = strpos($source, 'in_array($action, $allowedactions');
        $this->assertNotFalse($guardpos, 'Could not find allow-list guard in dispatcher');

        $fileexistspos = strpos($source, 'file_exists(');
        $this->assertNotFalse($fileexistspos, 'Could not find file_exists() in dispatcher');

        $this->assertLessThan(
            $fileexistspos,
            $guardpos,
            'The allow-list guard must appear before file_exists() so unknown actions are rejected without touching the filesystem'
        );
    }

    /**
     * Every action leaf that ships in ajax/ is whitelisted.
     *
     * @return void
     * @coversNothing
     */
    public function test_every_action_leaf_is_whitelisted(): void {
        $ajaxdir = __DIR__ . '/../../ajax';
        $leaves = glob($ajaxdir . '/*.php') ?: [];
        // Strip the dispatcher itself.
        $leaves = array_filter($leaves, fn($f) => basename($f) !== 'ajax.php');

        $source = file_get_contents(self::DISPATCHER);
        $this->assertNotFalse($source, 'Could not read ajax.php');

        foreach ($leaves as $leaf) {
            $name = basename($leaf, '.php');
            $this->assertMatchesRegularExpression(
                "/['\"]" . preg_quote($name, '/') . "['\"]/",
                $source,
                "Action '$name' must be listed in \$allowedactions"
            );
        }
    }
}
