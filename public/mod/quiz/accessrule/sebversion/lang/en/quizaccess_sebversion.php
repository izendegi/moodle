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

/**
 * The language strings for the quizaccess_sebversion plugin.
 *
 * @package    quizaccess_sebversion
 * @copyright  2025, Philipp E. Imhof
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['enforcedefault'] = 'Enabled by default';
$string['enforcedefault_desc'] = 'If activated, this will enable the setting for all new quizzes. Note that the plugin will not have any effect for any quiz that does not require the use of Safe Exam Browser. Also, it will not affect existing quizzes.';
$string['minversionmac'] = 'Minimum version (macOS)';
$string['minversionmac_desc'] = 'The minimum acceptable version on macOS devices, including iPad. Specifying a bad version might stop quizzes from working.';
$string['minversionwin'] = 'Minimum version (Windows)';
$string['minversionwin_desc'] = 'The minimum acceptable version on Windows devices. Specifying a bad version might stop quizzes from working.';
$string['overlay_text_invalid'] = 'The version of your Safe Exam Browser could not be determined. Please download the most recent official version and try again.';
$string['overlay_text_update'] = 'Please update your Safe Exam Browser in order to attempt this quiz. You need at least version {$a->version}.';
$string['pluginname'] = 'Enforce minimum version for Safe Exam Browser';
$string['privacy:metadata'] = 'The quizaccess_sebversion ("Enforce minimum version for Safe Exam Browser") plugin does not store any personal data.';
$string['sebversion_enforce'] = 'Enforce minimum SEB version';
$string['sebversion_enforce_help'] = 'If enabled, students will not be able to take the quiz unless their SEB version is at least {$a->win} (Windows) or {$a->mac} (Mac/iPad). Administrators can set the required minimum versions in the plugin settings.';
