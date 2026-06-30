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
 * Typeform module admin settings
 *
 * @package    mod_typeform
 * @copyright  2026 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/typeform/classes/typeform_api.php');

if ($ADMIN->fulltree) {
    // API url
    $settings->add(new admin_setting_configtext(
        'typeform/apiurl',
        get_string('apiurl', 'typeform'),
        get_string('apiurl_desc', 'typeform'),
        '',
        PARAM_TEXT
    ));

    // API Token
    $settings->add(new admin_setting_configpasswordunmask(
        'typeform/apitoken',
        get_string('apitoken', 'typeform'),
        get_string('apitoken_desc', 'typeform'),
        '',
        PARAM_TEXT
    ));

    // API typeformjslink
    $settings->add(new admin_setting_configtext(
        'typeform/typeformjslink',
        get_string('typeformjslink', 'typeform'),
        get_string('typeformjslink_desc', 'typeform'),
        'https://embed.typeform.com/next/embed.js',
        PARAM_TEXT
    ));

    // API typeformdomain
    $settings->add(new admin_setting_configtext(
        'typeform/typeformdomain',
        get_string('typeformdomain', 'typeform'),
        get_string('typeformdomain_desc', 'typeform'),
        'form.typeform.eu',
        PARAM_TEXT
    ));

    // Allowed Workspaces
    $settings->add(new admin_setting_configtext(
        'typeform/allowedworkspaces',
        get_string('allowedworkspaces', 'typeform'),
        get_string('allowedworkspaces_desc', 'typeform'),
        '',
        PARAM_TEXT
    ));

    // Test connection button
    $testurl = new moodle_url('/mod/typeform/test_connection.php');
    $settings->add(new admin_setting_description(
        'typeform/testconnection',
        get_string('testconnection', 'typeform'),
        html_writer::link($testurl, get_string('testconnection', 'typeform'), ['target' => '_blank'])
    ));
}
