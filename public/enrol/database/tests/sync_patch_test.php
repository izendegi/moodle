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
 * External database enrolment sync tests, this also tests adodb drivers
 * that are matching our four supported Moodle database drivers.
 *
 * @package    enrol_database
 * @category   phpunit
 * @copyright  2011 Petr Skoda {@link http://skodak.org}
 * @copyright  2020 Kepa Urzelai
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class enrol_database_patch_testcase{
    protected function test_sync_courses(){}

    protected function test_sync_enrolments(){}

    protected function test_sync_groups(){}

    protected function test_sync_group_enrolments(){}
}
