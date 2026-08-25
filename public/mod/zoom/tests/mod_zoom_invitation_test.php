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
 * Tests for the invitation class.
 *
 * @package    mod_zoom
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_zoom;

use advanced_testcase;
use context_system;
use context_course;
use moodle_url;

/**
 * PHPunit testcase class for invitations.
 * @covers \mod_zoom\invitation
 */
final class mod_zoom_invitation_test extends advanced_testcase {
    /**
     * Setup to ensure that fixtures are loaded.
     */
    public static function setUpBeforeClass(): void {
        global $CFG;
        require_once($CFG->libdir . '/accesslib.php');
        parent::setUpBeforeClass();
    }

    /**
     * Test zoom invitation display message for user with all capabilities.
     */
    public function test_display_message_when_user_has_all_capabilities(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $zoom = $this->getDataGenerator()->create_module('zoom', ['course' => $course]);
        $message = (new invitation(
            $this->get_mock_invitation_message_scheduledmeeting()
        ))->get_display_string($zoom->cmid);
        $expectedmessage = "Organization is inviting you to a scheduled Zoom meeting.\n"
            . "\n"
            . "Topic: Zoom Meeting\n"
            . "Time: Mar 15, 2021 06:08 AM London\n"
            . "\n"
            . "Join Zoom Meeting\n"
            . "https://us02web.zoom.us/j/12341234123?pwd=THBLWExVS0QyYnV1Z1nZTDJGYVI2QT09\n"
            . "\n"
            . "Meeting ID: 123 1234 1234\n"
            . "Passcode: 123123\n"
            . "\n"
            . "One tap mobile\n"
            . "+61323452345,,12341234123#,,,,*123456# Australia\n"
            . "+61312341234,,12341234123#,,,,*123456# Australia\n"
            . "\n"
            . "Dial by your location\n"
            . "        +61 3 5678 5678 Australia\n"
            . "        +61 3 4567 4567 Australia\n"
            . "        +61 3 3456 3456 Australia\n"
            . "        +61 3 2345 2345 Australia\n"
            . "        +61 3 1234 1234 Australia\n"
            . "Meeting ID: 123 1234 1234\n"
            . "Passcode: 123456\n"
            . "Find your local number: https://us02web.zoom.us/u/abcde12345\n"
            . "\n"
            . "Join by SIP\n"
            . "1231231231234@zoomabc.com\n"
            . "\n"
            . "Join by H.323\n"
            . "162.255.37.11 (US West)\n"
            . "162.255.36.11 (US East)\n"
            . "115.114.131.7 (India Mumbai)\n"
            . "115.114.115.7 (India Hyderabad)\n"
            . "213.19.144.110 (Amsterdam Netherlands)\n"
            . "213.244.140.110 (Germany)\n"
            . "103.122.166.55 (Australia Sydney)\n"
            . "103.122.167.55 (Australia Melbourne)\n"
            . "149.137.40.110 (Singapore)\n"
            . "64.211.144.160 (Brazil)\n"
            . "69.174.57.160 (Canada Toronto)\n"
            . "65.39.152.160 (Canada Vancouver)\n"
            . "207.226.132.110 (Japan Tokyo)\n"
            . "149.137.24.110 (Japan Osaka)\n"
            . "Meeting ID: 966 7989 3993\n"
            . "Passcode: 44730043";
        $this->assertEquals($expectedmessage, $message);
    }

    /**
     * Test zoom invitation display message for user with only the mod/zoom:viewjoinurl capability.
     */
    public function test_display_message_when_user_has_viewjoinurl_capability(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $role = $this->getDataGenerator()->create_role();
        $zoom = $this->getDataGenerator()->create_module('zoom', ['course' => $course]);
        assign_capability('mod/zoom:viewjoinurl', CAP_ALLOW, $role, context_system::instance()->id);
        role_assign($role, $user->id, context_course::instance($course->id));
        $message = (new invitation(
            $this->get_mock_invitation_message_scheduledmeeting()
        ))->get_display_string($zoom->cmid, $user->id);
        $expectedmessage = "Organization is inviting you to a scheduled Zoom meeting.\n"
            . "\n"
            . "Topic: Zoom Meeting\n"
            . "Time: Mar 15, 2021 06:08 AM London\n"
            . "\n"
            . "Join Zoom Meeting\n"
            . "https://us02web.zoom.us/j/12341234123?pwd=THBLWExVS0QyYnV1Z1nZTDJGYVI2QT09\n"
            . "\n"
            . "Meeting ID: 123 1234 1234\n"
            . "Passcode: 123123";
        $this->assertEquals($expectedmessage, $message);
    }

    /**
     * Test zoom invitation display message for user with only the mod/zoom:viewdialin capability.
     */
    public function test_display_message_when_user_has_viewdialin_capability(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $role = $this->getDataGenerator()->create_role();
        $zoom = $this->getDataGenerator()->create_module('zoom', ['course' => $course]);
        assign_capability('mod/zoom:viewdialin', CAP_ALLOW, $role, context_system::instance());
        role_assign($role, $user->id, context_course::instance($course->id));
        $message = (new invitation(
            $this->get_mock_invitation_message_scheduledmeeting()
        ))->get_display_string($zoom->cmid, $user->id);
        $expectedmessage = "Organization is inviting you to a scheduled Zoom meeting.\n"
            . "\n"
            . "Topic: Zoom Meeting\n"
            . "Time: Mar 15, 2021 06:08 AM London\n"
            . "\n"
            . "One tap mobile\n"
            . "+61323452345,,12341234123#,,,,*123456# Australia\n"
            . "+61312341234,,12341234123#,,,,*123456# Australia\n"
            . "\n"
            . "Dial by your location\n"
            . "        +61 3 5678 5678 Australia\n"
            . "        +61 3 4567 4567 Australia\n"
            . "        +61 3 3456 3456 Australia\n"
            . "        +61 3 2345 2345 Australia\n"
            . "        +61 3 1234 1234 Australia\n"
            . "Meeting ID: 123 1234 1234\n"
            . "Passcode: 123456\n"
            . "Find your local number: https://us02web.zoom.us/u/abcde12345\n"
            . "\n"
            . "Join by SIP\n"
            . "1231231231234@zoomabc.com\n"
            . "\n"
            . "Join by H.323\n"
            . "162.255.37.11 (US West)\n"
            . "162.255.36.11 (US East)\n"
            . "115.114.131.7 (India Mumbai)\n"
            . "115.114.115.7 (India Hyderabad)\n"
            . "213.19.144.110 (Amsterdam Netherlands)\n"
            . "213.244.140.110 (Germany)\n"
            . "103.122.166.55 (Australia Sydney)\n"
            . "103.122.167.55 (Australia Melbourne)\n"
            . "149.137.40.110 (Singapore)\n"
            . "64.211.144.160 (Brazil)\n"
            . "69.174.57.160 (Canada Toronto)\n"
            . "65.39.152.160 (Canada Vancouver)\n"
            . "207.226.132.110 (Japan Tokyo)\n"
            . "149.137.24.110 (Japan Osaka)\n"
            . "Meeting ID: 966 7989 3993\n"
            . "Passcode: 44730043";
        $this->assertEquals($expectedmessage, $message);
    }

    /**
     * Test zoom invitation display message for user has no capabilities.
     */
    public function test_display_message_when_user_has_no_capabilities(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $role = $this->getDataGenerator()->create_role();
        $zoom = $this->getDataGenerator()->create_module('zoom', ['course' => $course]);
        role_assign($role, $user->id, context_course::instance($course->id));
        $message = (new invitation(
            $this->get_mock_invitation_message_scheduledmeeting()
        ))->get_display_string($zoom->cmid, $user->id);
        $expectedmessage = null;
        $this->assertEquals($expectedmessage, $message);
    }

    /**
     * Test message if regex pattern is intentionally set to empty for an element.
     */
    public function test_display_message_when_a_regex_pattern_is_empty(): void {
        global $PAGE;
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('invitation_joinurl', '', 'zoom');
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $role = $this->getDataGenerator()->create_role();
        $zoom = $this->getDataGenerator()->create_module('zoom', ['course' => $course]);
        role_assign($role, $user->id, context_course::instance($course->id));
        // Set mock zoom activity URL for page as exception messages expect it.
        $PAGE->set_url(new moodle_url('/mod/zoom/view.php?id=123'));
        $message = (new invitation(
            $this->get_mock_invitation_message_scheduledmeeting()
        ))->get_display_string($zoom->cmid, $user->id);
        $this->assertDebuggingNotCalled();
    }

    /**
     * Test debug message if regex pattern is not valid for an element.
     */
    public function test_display_message_when_a_regex_pattern_is_invalid(): void {
        global $PAGE;
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('invitation_joinurl', '~', 'zoom');
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $role = $this->getDataGenerator()->create_role();
        $zoom = $this->getDataGenerator()->create_module('zoom', ['course' => $course]);
        assign_capability('mod/zoom:viewdialin', CAP_ALLOW, $role, context_system::instance());
        role_assign($role, $user->id, context_course::instance($course->id));
        // Set mock zoom activity URL for page as exception messages expect it.
        $PAGE->set_url(new moodle_url('/mod/zoom/view.php?id=123'));
        $message = (new invitation(
            $this->get_mock_invitation_message_scheduledmeeting()
        ))->get_display_string($zoom->cmid, $user->id);
        $this->assertDebuggingCalled('Error in regex for zoom invitation element: "joinurl" with pattern: "~".');
    }

    /**
     * Test debug message if no match is found using regex pattern for an element.
     */
    public function test_display_message_when_a_regex_pattern_is_finds_no_match(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('invitation_joinurl', '/nomatch/mi', 'zoom');
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $role = $this->getDataGenerator()->create_role();
        $zoom = $this->getDataGenerator()->create_module('zoom', ['course' => $course]);
        assign_capability('mod/zoom:viewdialin', CAP_ALLOW, $role, context_system::instance());
        role_assign($role, $user->id, context_course::instance($course->id));
        $message = (new invitation(
            $this->get_mock_invitation_message_scheduledmeeting()
        ))->get_display_string($zoom->cmid, $user->id);
        $this->assertDebuggingCalled('No match found in zoom invitation for element: "joinurl" with pattern: "/nomatch/mi".');
    }

    /**
     * Test removing the invite sentence from the zoom meeting message.
     */
    public function test_display_message_has_invite_removed_if_setting_enabled(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('invitationremoveinvite', '1', 'zoom');
        $course = $this->getDataGenerator()->create_course();
        $zoom = $this->getDataGenerator()->create_module('zoom', ['course' => $course]);
        $message = (new invitation(
            $this->get_mock_invitation_message_scheduledmeeting()
        ))->get_display_string($zoom->cmid);
        $expectedmessage = "Topic: Zoom Meeting\n"
            . "Time: Mar 15, 2021 06:08 AM London\n"
            . "\n"
            . "Join Zoom Meeting\n"
            . "https://us02web.zoom.us/j/12341234123?pwd=THBLWExVS0QyYnV1Z1nZTDJGYVI2QT09\n"
            . "\n"
            . "Meeting ID: 123 1234 1234\n"
            . "Passcode: 123123\n"
            . "\n"
            . "One tap mobile\n"
            . "+61323452345,,12341234123#,,,,*123456# Australia\n"
            . "+61312341234,,12341234123#,,,,*123456# Australia\n"
            . "\n"
            . "Dial by your location\n"
            . "        +61 3 5678 5678 Australia\n"
            . "        +61 3 4567 4567 Australia\n"
            . "        +61 3 3456 3456 Australia\n"
            . "        +61 3 2345 2345 Australia\n"
            . "        +61 3 1234 1234 Australia\n"
            . "Meeting ID: 123 1234 1234\n"
            . "Passcode: 123456\n"
            . "Find your local number: https://us02web.zoom.us/u/abcde12345\n"
            . "\n"
            . "Join by SIP\n"
            . "1231231231234@zoomabc.com\n"
            . "\n"
            . "Join by H.323\n"
            . "162.255.37.11 (US West)\n"
            . "162.255.36.11 (US East)\n"
            . "115.114.131.7 (India Mumbai)\n"
            . "115.114.115.7 (India Hyderabad)\n"
            . "213.19.144.110 (Amsterdam Netherlands)\n"
            . "213.244.140.110 (Germany)\n"
            . "103.122.166.55 (Australia Sydney)\n"
            . "103.122.167.55 (Australia Melbourne)\n"
            . "149.137.40.110 (Singapore)\n"
            . "64.211.144.160 (Brazil)\n"
            . "69.174.57.160 (Canada Toronto)\n"
            . "65.39.152.160 (Canada Vancouver)\n"
            . "207.226.132.110 (Japan Tokyo)\n"
            . "149.137.24.110 (Japan Osaka)\n"
            . "Meeting ID: 966 7989 3993\n"
            . "Passcode: 44730043";
        $this->assertEquals($expectedmessage, $message);
    }

    /**
     * Test not removing the invite sentence from the zoom meeting message.
     */
    public function test_display_message_does_not_have_invite_removed_if_setting_disabled(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('invitationremoveinvite', '0', 'zoom');
        $course = $this->getDataGenerator()->create_course();
        $zoom = $this->getDataGenerator()->create_module('zoom', ['course' => $course]);
        $message = (new invitation(
            $this->get_mock_invitation_message_scheduledmeeting()
        ))->get_display_string($zoom->cmid);
        $expectedmessage = "Organization is inviting you to a scheduled Zoom meeting.\n"
            . "\n"
            . "Topic: Zoom Meeting\n"
            . "Time: Mar 15, 2021 06:08 AM London\n"
            . "\n"
            . "Join Zoom Meeting\n"
            . "https://us02web.zoom.us/j/12341234123?pwd=THBLWExVS0QyYnV1Z1nZTDJGYVI2QT09\n"
            . "\n"
            . "Meeting ID: 123 1234 1234\n"
            . "Passcode: 123123\n"
            . "\n"
            . "One tap mobile\n"
            . "+61323452345,,12341234123#,,,,*123456# Australia\n"
            . "+61312341234,,12341234123#,,,,*123456# Australia\n"
            . "\n"
            . "Dial by your location\n"
            . "        +61 3 5678 5678 Australia\n"
            . "        +61 3 4567 4567 Australia\n"
            . "        +61 3 3456 3456 Australia\n"
            . "        +61 3 2345 2345 Australia\n"
            . "        +61 3 1234 1234 Australia\n"
            . "Meeting ID: 123 1234 1234\n"
            . "Passcode: 123456\n"
            . "Find your local number: https://us02web.zoom.us/u/abcde12345\n"
            . "\n"
            . "Join by SIP\n"
            . "1231231231234@zoomabc.com\n"
            . "\n"
            . "Join by H.323\n"
            . "162.255.37.11 (US West)\n"
            . "162.255.36.11 (US East)\n"
            . "115.114.131.7 (India Mumbai)\n"
            . "115.114.115.7 (India Hyderabad)\n"
            . "213.19.144.110 (Amsterdam Netherlands)\n"
            . "213.244.140.110 (Germany)\n"
            . "103.122.166.55 (Australia Sydney)\n"
            . "103.122.167.55 (Australia Melbourne)\n"
            . "149.137.40.110 (Singapore)\n"
            . "64.211.144.160 (Brazil)\n"
            . "69.174.57.160 (Canada Toronto)\n"
            . "65.39.152.160 (Canada Vancouver)\n"
            . "207.226.132.110 (Japan Tokyo)\n"
            . "149.137.24.110 (Japan Osaka)\n"
            . "Meeting ID: 966 7989 3993\n"
            . "Passcode: 44730043";
        $this->assertEquals($expectedmessage, $message);
    }

    /**
     * Test removing the iCal link from the zoom meeting message.
     */
    public function test_display_message_has_icallink_removed_if_setting_enabled(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('invitationremoveicallink', '1', 'zoom');
        $course = $this->getDataGenerator()->create_course();
        $zoom = $this->getDataGenerator()->create_module('zoom', ['course' => $course]);

        // Test a scheduled meeting.
        $message = (new invitation(
            $this->get_mock_invitation_message_scheduledmeeting()
        ))->get_display_string($zoom->cmid);
        $expectedmessage = "Organization is inviting you to a scheduled Zoom meeting.\n"
            . "\n"
            . "Topic: Zoom Meeting\n"
            . "Time: Mar 15, 2021 06:08 AM London\n"
            . "\n"
            . "Join Zoom Meeting\n"
            . "https://us02web.zoom.us/j/12341234123?pwd=THBLWExVS0QyYnV1Z1nZTDJGYVI2QT09\n"
            . "\n"
            . "Meeting ID: 123 1234 1234\n"
            . "Passcode: 123123\n"
            . "\n"
            . "One tap mobile\n"
            . "+61323452345,,12341234123#,,,,*123456# Australia\n"
            . "+61312341234,,12341234123#,,,,*123456# Australia\n"
            . "\n"
            . "Dial by your location\n"
            . "        +61 3 5678 5678 Australia\n"
            . "        +61 3 4567 4567 Australia\n"
            . "        +61 3 3456 3456 Australia\n"
            . "        +61 3 2345 2345 Australia\n"
            . "        +61 3 1234 1234 Australia\n"
            . "Meeting ID: 123 1234 1234\n"
            . "Passcode: 123456\n"
            . "Find your local number: https://us02web.zoom.us/u/abcde12345\n"
            . "\n"
            . "Join by SIP\n"
            . "1231231231234@zoomabc.com\n"
            . "\n"
            . "Join by H.323\n"
            . "162.255.37.11 (US West)\n"
            . "162.255.36.11 (US East)\n"
            . "115.114.131.7 (India Mumbai)\n"
            . "115.114.115.7 (India Hyderabad)\n"
            . "213.19.144.110 (Amsterdam Netherlands)\n"
            . "213.244.140.110 (Germany)\n"
            . "103.122.166.55 (Australia Sydney)\n"
            . "103.122.167.55 (Australia Melbourne)\n"
            . "149.137.40.110 (Singapore)\n"
            . "64.211.144.160 (Brazil)\n"
            . "69.174.57.160 (Canada Toronto)\n"
            . "65.39.152.160 (Canada Vancouver)\n"
            . "207.226.132.110 (Japan Tokyo)\n"
            . "149.137.24.110 (Japan Osaka)\n"
            . "Meeting ID: 966 7989 3993\n"
            . "Passcode: 44730043";
        $this->assertEquals($expectedmessage, $message);

        // Test a recurring meeting with no fixed time.
        $message = (new invitation(
            $this->get_mock_invitation_message_recurringnofixed()
        ))->get_display_string($zoom->cmid);
        $expectedmessage = "Organization is inviting you to a scheduled Zoom meeting.\n"
            . "\n"
            . "Topic: Zoom Meeting\n"
            . "Time: This is a recurring meeting Meet anytime\n"
            . "\n"
            . "Join Zoom Meeting\n"
            . "https://us02web.zoom.us/j/12341234123?pwd=THBLWExVS0QyYnV1Z1nZTDJGYVI2QT09\n"
            . "\n"
            . "Meeting ID: 123 1234 1234\n"
            . "Passcode: 123123\n"
            . "\n"
            . "One tap mobile\n"
            . "+61323452345,,12341234123#,,,,*123456# Australia\n"
            . "+61312341234,,12341234123#,,,,*123456# Australia\n"
            . "\n"
            . "Dial by your location\n"
            . "        +61 3 5678 5678 Australia\n"
            . "        +61 3 4567 4567 Australia\n"
            . "        +61 3 3456 3456 Australia\n"
            . "        +61 3 2345 2345 Australia\n"
            . "        +61 3 1234 1234 Australia\n"
            . "Meeting ID: 123 1234 1234\n"
            . "Passcode: 123456\n"
            . "Find your local number: https://us02web.zoom.us/u/abcde12345\n"
            . "\n"
            . "Join by SIP\n"
            . "1231231231234@zoomabc.com\n"
            . "\n"
            . "Join by H.323\n"
            . "162.255.37.11 (US West)\n"
            . "162.255.36.11 (US East)\n"
            . "115.114.131.7 (India Mumbai)\n"
            . "115.114.115.7 (India Hyderabad)\n"
            . "213.19.144.110 (Amsterdam Netherlands)\n"
            . "213.244.140.110 (Germany)\n"
            . "103.122.166.55 (Australia Sydney)\n"
            . "103.122.167.55 (Australia Melbourne)\n"
            . "149.137.40.110 (Singapore)\n"
            . "64.211.144.160 (Brazil)\n"
            . "69.174.57.160 (Canada Toronto)\n"
            . "65.39.152.160 (Canada Vancouver)\n"
            . "207.226.132.110 (Japan Tokyo)\n"
            . "149.137.24.110 (Japan Osaka)\n"
            . "Meeting ID: 966 7989 3993\n"
            . "Passcode: 44730043";
        $this->assertEquals($expectedmessage, $message);

        // Test a recurring meeting with fixed time.
        $message = (new invitation($this->get_mock_invitation_message_recurringfixed()))->get_display_string($zoom->cmid);
        $expectedmessage = "Organization is inviting you to a scheduled Zoom meeting.\n"
            . "\n"
            . "Topic: Zoom Meeting\n"
            . "Time: Mar 15, 2021 06:08 AM London\n"
            . "        Every day, until Mar 17, 2021, 3 occurrence(s)\n"
            . "        Mar 15, 2021 06:08 AM\n"
            . "        Mar 16, 2021 06:08 AM\n"
            . "        Mar 17, 2021 06:08 AM\n"
            . "\n"
            . "Join Zoom Meeting\n"
            . "https://us02web.zoom.us/j/12341234123?pwd=THBLWExVS0QyYnV1Z1nZTDJGYVI2QT09\n"
            . "\n"
            . "Meeting ID: 123 1234 1234\n"
            . "Passcode: 123123\n"
            . "\n"
            . "One tap mobile\n"
            . "+61323452345,,12341234123#,,,,*123456# Australia\n"
            . "+61312341234,,12341234123#,,,,*123456# Australia\n"
            . "\n"
            . "Dial by your location\n"
            . "        +61 3 5678 5678 Australia\n"
            . "        +61 3 4567 4567 Australia\n"
            . "        +61 3 3456 3456 Australia\n"
            . "        +61 3 2345 2345 Australia\n"
            . "        +61 3 1234 1234 Australia\n"
            . "Meeting ID: 123 1234 1234\n"
            . "Passcode: 123456\n"
            . "Find your local number: https://us02web.zoom.us/u/abcde12345\n"
            . "\n"
            . "Join by SIP\n"
            . "1231231231234@zoomabc.com\n"
            . "\n"
            . "Join by H.323\n"
            . "162.255.37.11 (US West)\n"
            . "162.255.36.11 (US East)\n"
            . "115.114.131.7 (India Mumbai)\n"
            . "115.114.115.7 (India Hyderabad)\n"
            . "213.19.144.110 (Amsterdam Netherlands)\n"
            . "213.244.140.110 (Germany)\n"
            . "103.122.166.55 (Australia Sydney)\n"
            . "103.122.167.55 (Australia Melbourne)\n"
            . "149.137.40.110 (Singapore)\n"
            . "64.211.144.160 (Brazil)\n"
            . "69.174.57.160 (Canada Toronto)\n"
            . "65.39.152.160 (Canada Vancouver)\n"
            . "207.226.132.110 (Japan Tokyo)\n"
            . "149.137.24.110 (Japan Osaka)\n"
            . "Meeting ID: 966 7989 3993\n"
            . "Passcode: 44730043";
        $this->assertEquals($expectedmessage, $message);

        // We expect that the debugging was called two times:
        // - one time for the scheduled meeting
        // - one time for the recurring meeting with no fixed time.
        $debugmessage = 'No match found in zoom invitation for element: "icallink"'
            . ' with pattern: "/^.+download and import the following iCalendar.+$\n.+$/mi".';
        $this->assertDebuggingCalledCount(2, [$debugmessage, $debugmessage]);
    }

    /**
     * Test not removing the iCal link from the zoom meeting message.
     */
    public function test_display_message_does_not_have_icallink_removed_if_setting_disabled(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('invitationremoveicallink', '0', 'zoom');
        $course = $this->getDataGenerator()->create_course();
        $zoom = $this->getDataGenerator()->create_module('zoom', ['course' => $course]);

        // Test a scheduled meeting.
        $message = (new invitation(
            $this->get_mock_invitation_message_scheduledmeeting()
        ))->get_display_string($zoom->cmid);
        $expectedmessage = "Organization is inviting you to a scheduled Zoom meeting.\n"
            . "\n"
            . "Topic: Zoom Meeting\n"
            . "Time: Mar 15, 2021 06:08 AM London\n"
            . "\n"
            . "Join Zoom Meeting\n"
            . "https://us02web.zoom.us/j/12341234123?pwd=THBLWExVS0QyYnV1Z1nZTDJGYVI2QT09\n"
            . "\n"
            . "Meeting ID: 123 1234 1234\n"
            . "Passcode: 123123\n"
            . "\n"
            . "One tap mobile\n"
            . "+61323452345,,12341234123#,,,,*123456# Australia\n"
            . "+61312341234,,12341234123#,,,,*123456# Australia\n"
            . "\n"
            . "Dial by your location\n"
            . "        +61 3 5678 5678 Australia\n"
            . "        +61 3 4567 4567 Australia\n"
            . "        +61 3 3456 3456 Australia\n"
            . "        +61 3 2345 2345 Australia\n"
            . "        +61 3 1234 1234 Australia\n"
            . "Meeting ID: 123 1234 1234\n"
            . "Passcode: 123456\n"
            . "Find your local number: https://us02web.zoom.us/u/abcde12345\n"
            . "\n"
            . "Join by SIP\n"
            . "1231231231234@zoomabc.com\n"
            . "\n"
            . "Join by H.323\n"
            . "162.255.37.11 (US West)\n"
            . "162.255.36.11 (US East)\n"
            . "115.114.131.7 (India Mumbai)\n"
            . "115.114.115.7 (India Hyderabad)\n"
            . "213.19.144.110 (Amsterdam Netherlands)\n"
            . "213.244.140.110 (Germany)\n"
            . "103.122.166.55 (Australia Sydney)\n"
            . "103.122.167.55 (Australia Melbourne)\n"
            . "149.137.40.110 (Singapore)\n"
            . "64.211.144.160 (Brazil)\n"
            . "69.174.57.160 (Canada Toronto)\n"
            . "65.39.152.160 (Canada Vancouver)\n"
            . "207.226.132.110 (Japan Tokyo)\n"
            . "149.137.24.110 (Japan Osaka)\n"
            . "Meeting ID: 966 7989 3993\n"
            . "Passcode: 44730043";
        $this->assertEquals($expectedmessage, $message);

        // Test a recurring meeting with no fixed time.
        $message = (new invitation(
            $this->get_mock_invitation_message_recurringnofixed()
        ))->get_display_string($zoom->cmid);
        $expectedmessage = "Organization is inviting you to a scheduled Zoom meeting.\n"
            . "\n"
            . "Topic: Zoom Meeting\n"
            . "Time: This is a recurring meeting Meet anytime\n"
            . "\n"
            . "Join Zoom Meeting\n"
            . "https://us02web.zoom.us/j/12341234123?pwd=THBLWExVS0QyYnV1Z1nZTDJGYVI2QT09\n"
            . "\n"
            . "Meeting ID: 123 1234 1234\n"
            . "Passcode: 123123\n"
            . "\n"
            . "One tap mobile\n"
            . "+61323452345,,12341234123#,,,,*123456# Australia\n"
            . "+61312341234,,12341234123#,,,,*123456# Australia\n"
            . "\n"
            . "Dial by your location\n"
            . "        +61 3 5678 5678 Australia\n"
            . "        +61 3 4567 4567 Australia\n"
            . "        +61 3 3456 3456 Australia\n"
            . "        +61 3 2345 2345 Australia\n"
            . "        +61 3 1234 1234 Australia\n"
            . "Meeting ID: 123 1234 1234\n"
            . "Passcode: 123456\n"
            . "Find your local number: https://us02web.zoom.us/u/abcde12345\n"
            . "\n"
            . "Join by SIP\n"
            . "1231231231234@zoomabc.com\n"
            . "\n"
            . "Join by H.323\n"
            . "162.255.37.11 (US West)\n"
            . "162.255.36.11 (US East)\n"
            . "115.114.131.7 (India Mumbai)\n"
            . "115.114.115.7 (India Hyderabad)\n"
            . "213.19.144.110 (Amsterdam Netherlands)\n"
            . "213.244.140.110 (Germany)\n"
            . "103.122.166.55 (Australia Sydney)\n"
            . "103.122.167.55 (Australia Melbourne)\n"
            . "149.137.40.110 (Singapore)\n"
            . "64.211.144.160 (Brazil)\n"
            . "69.174.57.160 (Canada Toronto)\n"
            . "65.39.152.160 (Canada Vancouver)\n"
            . "207.226.132.110 (Japan Tokyo)\n"
            . "149.137.24.110 (Japan Osaka)\n"
            . "Meeting ID: 966 7989 3993\n"
            . "Passcode: 44730043";
        $this->assertEquals($expectedmessage, $message);

        // Test a recurring meeting with fixed time.
        $message = (new invitation($this->get_mock_invitation_message_recurringfixed()))->get_display_string($zoom->cmid);
        $expectedmessage = "Organization is inviting you to a scheduled Zoom meeting.\n"
            . "\n"
            . "Topic: Zoom Meeting\n"
            . "Time: Mar 15, 2021 06:08 AM London\n"
            . "        Every day, until Mar 17, 2021, 3 occurrence(s)\n"
            . "        Mar 15, 2021 06:08 AM\n"
            . "        Mar 16, 2021 06:08 AM\n"
            . "        Mar 17, 2021 06:08 AM\n"
            . "Please download and import the following iCalendar (.ics) files to your calendar system.\n"
            . "Zoom Meeting: https://us02web.zoom.us/meeting/u7Utd-GqqTkiEtEkvHci3z_X0W3TMjX_Avua/ics?icsToken="
            . "88tyKu-prjsiGNOXuBGCR_McAoigWerwtnpHj7d4lwnNACkAeDzOZtURBp91KOfx\n"
            . "\n"
            . "Join Zoom Meeting\n"
            . "https://us02web.zoom.us/j/12341234123?pwd=THBLWExVS0QyYnV1Z1nZTDJGYVI2QT09\n"
            . "\n"
            . "Meeting ID: 123 1234 1234\n"
            . "Passcode: 123123\n"
            . "\n"
            . "One tap mobile\n"
            . "+61323452345,,12341234123#,,,,*123456# Australia\n"
            . "+61312341234,,12341234123#,,,,*123456# Australia\n"
            . "\n"
            . "Dial by your location\n"
            . "        +61 3 5678 5678 Australia\n"
            . "        +61 3 4567 4567 Australia\n"
            . "        +61 3 3456 3456 Australia\n"
            . "        +61 3 2345 2345 Australia\n"
            . "        +61 3 1234 1234 Australia\n"
            . "Meeting ID: 123 1234 1234\n"
            . "Passcode: 123456\n"
            . "Find your local number: https://us02web.zoom.us/u/abcde12345\n"
            . "\n"
            . "Join by SIP\n"
            . "1231231231234@zoomabc.com\n"
            . "\n"
            . "Join by H.323\n"
            . "162.255.37.11 (US West)\n"
            . "162.255.36.11 (US East)\n"
            . "115.114.131.7 (India Mumbai)\n"
            . "115.114.115.7 (India Hyderabad)\n"
            . "213.19.144.110 (Amsterdam Netherlands)\n"
            . "213.244.140.110 (Germany)\n"
            . "103.122.166.55 (Australia Sydney)\n"
            . "103.122.167.55 (Australia Melbourne)\n"
            . "149.137.40.110 (Singapore)\n"
            . "64.211.144.160 (Brazil)\n"
            . "69.174.57.160 (Canada Toronto)\n"
            . "65.39.152.160 (Canada Vancouver)\n"
            . "207.226.132.110 (Japan Tokyo)\n"
            . "149.137.24.110 (Japan Osaka)\n"
            . "Meeting ID: 966 7989 3993\n"
            . "Passcode: 44730043";
        $this->assertEquals($expectedmessage, $message);
    }

    /**
     * Test get_display_string returns null without throwing an error if the invitation string provided is null.
     */
    public function test_display_message_when_instantiated_with_null_zoom_meeting_invitation(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $zoom = $this->getDataGenerator()->create_module('zoom', ['course' => $course]);
        $message = (new invitation(null))->get_display_string($zoom->cmid);
        $this->assertNull($message);
    }

    /**
     * Get a mock zoom invitation email message for a scheduled meeting.
     *
     * @return string
     */
    private function get_mock_invitation_message_scheduledmeeting(): string {
        return "Organization is inviting you to a scheduled Zoom meeting.\n"
            . "\n"
            . "Topic: Zoom Meeting\n"
            . "Time: Mar 15, 2021 06:08 AM London\n"
            . "\n"
            . "Join Zoom Meeting\n"
            . "https://us02web.zoom.us/j/12341234123?pwd=THBLWExVS0QyYnV1Z1nZTDJGYVI2QT09\n"
            . "\n"
            . "Meeting ID: 123 1234 1234\n"
            . "Passcode: 123123\n"
            . "One tap mobile\n"
            . "+61323452345,,12341234123#,,,,*123456# Australia\n"
            . "+61312341234,,12341234123#,,,,*123456# Australia\n"
            . "\n"
            . "Dial by your location\n"
            . "        +61 3 5678 5678 Australia\n"
            . "        +61 3 4567 4567 Australia\n"
            . "        +61 3 3456 3456 Australia\n"
            . "        +61 3 2345 2345 Australia\n"
            . "        +61 3 1234 1234 Australia\n"
            . "Meeting ID: 123 1234 1234\n"
            . "Passcode: 123456\n"
            . "Find your local number: https://us02web.zoom.us/u/abcde12345\n"
            . "\n"
            . "Join by SIP\n"
            . "1231231231234@zoomabc.com\n"
            . "\n"
            . "Join by H.323\n"
            . "162.255.37.11 (US West)\n"
            . "162.255.36.11 (US East)\n"
            . "115.114.131.7 (India Mumbai)\n"
            . "115.114.115.7 (India Hyderabad)\n"
            . "213.19.144.110 (Amsterdam Netherlands)\n"
            . "213.244.140.110 (Germany)\n"
            . "103.122.166.55 (Australia Sydney)\n"
            . "103.122.167.55 (Australia Melbourne)\n"
            . "149.137.40.110 (Singapore)\n"
            . "64.211.144.160 (Brazil)\n"
            . "69.174.57.160 (Canada Toronto)\n"
            . "65.39.152.160 (Canada Vancouver)\n"
            . "207.226.132.110 (Japan Tokyo)\n"
            . "149.137.24.110 (Japan Osaka)\n"
            . "Meeting ID: 966 7989 3993\n"
            . "Passcode: 44730043";
    }

    /**
     * Get a mock zoom invitation email message for a recurring meeting with no fixed time.
     *
     * @return string
     */
    private function get_mock_invitation_message_recurringnofixed(): string {
        return "Organization is inviting you to a scheduled Zoom meeting.\n"
            . "\n"
            . "Topic: Zoom Meeting\n"
            . "Time: This is a recurring meeting Meet anytime\n"
            . "\n"
            . "Join Zoom Meeting\n"
            . "https://us02web.zoom.us/j/12341234123?pwd=THBLWExVS0QyYnV1Z1nZTDJGYVI2QT09\n"
            . "\n"
            . "Meeting ID: 123 1234 1234\n"
            . "Passcode: 123123\n"
            . "One tap mobile\n"
            . "+61323452345,,12341234123#,,,,*123456# Australia\n"
            . "+61312341234,,12341234123#,,,,*123456# Australia\n"
            . "\n"
            . "Dial by your location\n"
            . "        +61 3 5678 5678 Australia\n"
            . "        +61 3 4567 4567 Australia\n"
            . "        +61 3 3456 3456 Australia\n"
            . "        +61 3 2345 2345 Australia\n"
            . "        +61 3 1234 1234 Australia\n"
            . "Meeting ID: 123 1234 1234\n"
            . "Passcode: 123456\n"
            . "Find your local number: https://us02web.zoom.us/u/abcde12345\n"
            . "\n"
            . "Join by SIP\n"
            . "1231231231234@zoomabc.com\n"
            . "\n"
            . "Join by H.323\n"
            . "162.255.37.11 (US West)\n"
            . "162.255.36.11 (US East)\n"
            . "115.114.131.7 (India Mumbai)\n"
            . "115.114.115.7 (India Hyderabad)\n"
            . "213.19.144.110 (Amsterdam Netherlands)\n"
            . "213.244.140.110 (Germany)\n"
            . "103.122.166.55 (Australia Sydney)\n"
            . "103.122.167.55 (Australia Melbourne)\n"
            . "149.137.40.110 (Singapore)\n"
            . "64.211.144.160 (Brazil)\n"
            . "69.174.57.160 (Canada Toronto)\n"
            . "65.39.152.160 (Canada Vancouver)\n"
            . "207.226.132.110 (Japan Tokyo)\n"
            . "149.137.24.110 (Japan Osaka)\n"
            . "Meeting ID: 966 7989 3993\n"
            . "Passcode: 44730043";
    }

    /**
     * Get a mock zoom invitation email message for a recurring meeting with fixed time.
     *
     * @return string
     */
    private function get_mock_invitation_message_recurringfixed(): string {
        return "Organization is inviting you to a scheduled Zoom meeting.\n"
            . "\n"
            . "Topic: Zoom Meeting\n"
            . "Time: Mar 15, 2021 06:08 AM London\n"
            . "        Every day, until Mar 17, 2021, 3 occurrence(s)\n"
            . "        Mar 15, 2021 06:08 AM\n"
            . "        Mar 16, 2021 06:08 AM\n"
            . "        Mar 17, 2021 06:08 AM\n"
            . "Please download and import the following iCalendar (.ics) files to your calendar system.\n"
            . "Zoom Meeting: https://us02web.zoom.us/meeting/u7Utd-GqqTkiEtEkvHci3z_X0W3TMjX_Avua/ics?icsToken="
            . "88tyKu-prjsiGNOXuBGCR_McAoigWerwtnpHj7d4lwnNACkAeDzOZtURBp91KOfx\n"
            . "\n"
            . "Join Zoom Meeting\n"
            . "https://us02web.zoom.us/j/12341234123?pwd=THBLWExVS0QyYnV1Z1nZTDJGYVI2QT09\n"
            . "\n"
            . "Meeting ID: 123 1234 1234\n"
            . "Passcode: 123123\n"
            . "One tap mobile\n"
            . "+61323452345,,12341234123#,,,,*123456# Australia\n"
            . "+61312341234,,12341234123#,,,,*123456# Australia\n"
            . "\n"
            . "Dial by your location\n"
            . "        +61 3 5678 5678 Australia\n"
            . "        +61 3 4567 4567 Australia\n"
            . "        +61 3 3456 3456 Australia\n"
            . "        +61 3 2345 2345 Australia\n"
            . "        +61 3 1234 1234 Australia\n"
            . "Meeting ID: 123 1234 1234\n"
            . "Passcode: 123456\n"
            . "Find your local number: https://us02web.zoom.us/u/abcde12345\n"
            . "\n"
            . "Join by SIP\n"
            . "1231231231234@zoomabc.com\n"
            . "\n"
            . "Join by H.323\n"
            . "162.255.37.11 (US West)\n"
            . "162.255.36.11 (US East)\n"
            . "115.114.131.7 (India Mumbai)\n"
            . "115.114.115.7 (India Hyderabad)\n"
            . "213.19.144.110 (Amsterdam Netherlands)\n"
            . "213.244.140.110 (Germany)\n"
            . "103.122.166.55 (Australia Sydney)\n"
            . "103.122.167.55 (Australia Melbourne)\n"
            . "149.137.40.110 (Singapore)\n"
            . "64.211.144.160 (Brazil)\n"
            . "69.174.57.160 (Canada Toronto)\n"
            . "65.39.152.160 (Canada Vancouver)\n"
            . "207.226.132.110 (Japan Tokyo)\n"
            . "149.137.24.110 (Japan Osaka)\n"
            . "Meeting ID: 966 7989 3993\n"
            . "Passcode: 44730043";
    }
}
