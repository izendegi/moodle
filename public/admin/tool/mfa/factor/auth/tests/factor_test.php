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

namespace factor_auth;

/**
 * Tests for auth factor.
 *
 * @covers \factor_auth\factor
 * @package factor_auth
 * @copyright   2026
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class factor_test extends \advanced_testcase {

    /**
     * Tests get_state when the current user object has no auth property.
     *
     * @covers ::get_state
     */
    public function test_get_state_without_auth_property(): void {
        global $USER;
        $this->resetAfterTest(true);

        unset($USER->auth);
        set_config('enabled', 1, 'factor_auth');
        set_config('goodauth', 'manual', 'factor_auth');

        $factor = new \factor_auth\factor('auth');
        $this->assertSame(\tool_mfa\plugininfo\factor::STATE_NEUTRAL, $factor->get_state());
    }
}
