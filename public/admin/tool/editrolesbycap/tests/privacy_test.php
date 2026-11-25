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

namespace tool_editrolesbycap;

use tool_editrolesbycap\privacy\provider;
use core_privacy\tests\provider_testcase;
use core_privacy\local\request\writer;

/**
 * Privacy provider tests class.
 *
 * @package tool_editrolesbycap
 * @copyright 2018 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \tool_editrolesbycap\privacy\provider
 */
final class privacy_test extends provider_testcase {
    /**
     * Basic setup for these tests.
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
    }

    /**
     * Ensure that export_user_preferences returns single preferences.
     *
     * @covers ::export_user_preferences
     */
    public function test_export_user_preferences_single(): void {
        // Define a user preference.
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        set_user_preference('definerole_showadvanced', true);

        // Validate exported data.
        provider::export_user_preferences($user->id);
        $context = \context_user::instance($user->id);

        $writer = writer::with_context($context);
        $this->assertTrue($writer->has_any_data());
        $prefs = $writer->get_user_preferences('tool_editrolesbycap');
        $this->assertCount(1, (array) $prefs);
        $this->assertEquals('Yes', $prefs->definerole_showadvanced->value);
        $this->assertEquals(
            get_string('privacy:metadata:preference:definerole_showadvanced', 'tool_editrolesbycap'),
            $prefs->definerole_showadvanced->description
        );
    }

    /**
     * Ensure that export_user_preferences returns no data if the user has no data.
     *
     * @covers ::export_user_preferences
     */
    public function test_export_user_preferences_not_defined(): void {
        $user = $this->getDataGenerator()->create_user();
        provider::export_user_preferences($user->id);
        $writer = writer::with_context(\context_user::instance($user->id));
        $this->assertFalse($writer->has_any_data());
    }
}
