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
 * This file keeps track of upgrades to the course summary block
 *
 * Sometimes, changes between versions involve alterations to database structures
 * and other major things that may break installations.
 *
 * The upgrade function in this file will attempt to perform all the necessary
 * actions to upgrade your older installation to the current version.
 *
 * If there's something it cannot do itself, it will tell you what you need to do.
 *
 * The commands in here will all be database-neutral, using the methods of
 * database_manager class
 *
 * Please do not forget to use upgrade_set_timeout()
 * before any action that may take longer time to finish.
 *
 * SMOWL install.
 *
 * @package     block_smowl
 * @author      Smowltech <info@smowltech.com>
 * @copyright   Smiley Owl Tech S.L.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Actions during the install of this block.
 */
function xmldb_block_smowl_install() {
    global $DB, $CFG;

    $name = 'blockheight';
    set_config($name, 340, 'block_smowl');

    $default = 0;
    $name = 'tracking';
    set_config($name, $default, 'block_smowl');

    $name = 'floatblock';
    set_config($name, $default, 'block_smowl');

    // Internal Settings.
    $name = 'viewsettingsinternal';
    set_config($name, $default, 'block_smowl');

    // Bulk actions.
    $name = 'bulkactions';
    set_config($name, $default, 'block_smowl');

    // Distinction of exam attempts.
    // Set not differentiate between different attempts of the same exam for a student.
    $name = 'attempttracking';
    set_config($name, $default, 'block_smowl');

    // Set URLs from smowl.
    require_once($CFG->dirroot . "/blocks/smowl/classes/smowl_connection.php");

    $name = 'urlstudentview';
    $default = block_smowl_connection::get_default_urlstudentview();
    set_config($name, $default, 'block_smowl');

    // New Settings.
    $default = 1;

    // Access Control.
    $name = 'accesscontrol';
    set_config($name, $default, 'block_smowl');

    // JWT Secret
    $name = 'smowljwtsecret';
    set_config($name, $default, 'block_smowl');

    // API URL and functions.
    $name = 'urlsmowlapi';
    $default = block_smowl_connection::get_default_urlsmowlapi();
    set_config($name, $default, 'block_smowl');

    $name = 'apilmssettings';
    $default = block_smowl_connection::get_default_apilmssettings();
    set_config($name, $default, 'block_smowl');

    $name = 'apiconfigclient';
    $default = block_smowl_connection::get_default_apiconfigclient();
    set_config($name, $default, 'block_smowl');

    $name = 'apilmssettingscustomer';
    $default = block_smowl_connection::get_default_apilmssettingscustomer();
    set_config($name, $default, 'block_smowl');

    $name = 'lmsid';
    $moodle = 1;
    $olms = 3;
    set_config($name, $moodle, 'block_smowl');

    // LTI API URL and functions redefine.
    $name = 'urlsmowlltiapi';
    $default = block_smowl_connection::get_default_urlsmowlltiapi();
    set_config($name, $default, 'block_smowl');

    $name = 'ltiapiapplications';
    $default = block_smowl_connection::get_default_ltiapiapplications();
    set_config($name, $default, 'block_smowl');

    $name = 'ltiapideployments';
    $default = block_smowl_connection::get_default_ltiapideployments();
    set_config($name, $default, 'block_smowl');

    // Install, por defecto configurar en la próxima entrada a configuración de SMOWL.
    $name = 'typeconfig';
    set_config($name, 3, 'block_smowl');

    // LTI Settings.
    $name = 'urlsmowlltitool';
    $default = block_smowl_connection::get_default_urlsmowlltitool();
    set_config($name, $default, 'block_smowl');

    $name = 'ltitoolinit';
    $default = block_smowl_connection::get_default_ltitoolinit();
    set_config($name, $default, 'block_smowl');

    $name = 'ltitoolversion';
    $default = block_smowl_connection::get_default_ltitoolversion();
    set_config($name, $default, 'block_smowl');

    $name = 'ltitoolkeytype';
    $default = block_smowl_connection::get_default_ltitoolkeytype();
    set_config($name, $default, 'block_smowl');

    $name = 'ltitoolpublickeyset';
    $default = block_smowl_connection::get_default_ltitoolpublickeyset();
    set_config($name, $default, 'block_smowl');

    $name = 'ltitoolinitiatelogin';
    $default = block_smowl_connection::get_default_ltitoolinitiatelogin();
    set_config($name, $default, 'block_smowl');

    $name = 'ltitoolredirection';
    $default = block_smowl_connection::get_default_ltitoolredirection();
    set_config($name, $default, 'block_smowl');

    $name = 'ltitoolconfigusage';
    $default = block_smowl_connection::get_default_ltitoolconfigusage();
    set_config($name, $default, 'block_smowl');

    $name = 'ltitoollaunch';
    $default = block_smowl_connection::get_default_ltitoollaunch();
    set_config($name, $default, 'block_smowl');

    $name = 'ltitoolconfigmemberships';
    $default = block_smowl_connection::get_default_ltitoolconfigmemberships();
    set_config($name, $default, 'block_smowl');

    $name = 'apiaddactivity';
    $default = block_smowl_connection::get_default_apiaddactivity();
    set_config($name, $default, 'block_smowl');

    $name = 'apimodifyactivity';
    $default = block_smowl_connection::get_default_apimodifyactivity();
    set_config($name, $default, 'block_smowl');

    update_capabilities('block_smowl');

    $roleid = create_role(
        'SMOWL WebService User',
        'smowlwsrole',
        'Role for web service access to SMOWL-related endpoints'
    );
    assign_capability('webservice/rest:use', CAP_ALLOW, $roleid, context_system::instance());
    assign_capability('block/smowl:manageactivities', CAP_ALLOW, $roleid, context_system::instance());
    assign_capability('moodle/course:viewhiddencourses', CAP_ALLOW, $roleid, context_system::instance());
    assign_capability('moodle/user:viewdetails', CAP_ALLOW, $roleid, context_system::instance());
    assign_capability('moodle/course:useremail', CAP_ALLOW, $roleid, context_system::instance());
    assign_capability('moodle/course:view', CAP_ALLOW, $roleid, context_system::instance());
    assign_capability('moodle/course:viewparticipants', CAP_ALLOW, $roleid, context_system::instance());
    assign_capability('moodle/site:accessallgroups', CAP_ALLOW, $roleid, context_system::instance());
    assign_capability('moodle/site:viewparticipants', CAP_ALLOW, $roleid, context_system::instance());
    assign_capability('mod/quiz:view', CAP_ALLOW, $roleid, context_system::instance());
    assign_capability('mod/workshop:view', CAP_ALLOW, $roleid, context_system::instance());
    assign_capability('mod/assign:view', CAP_ALLOW, $roleid, context_system::instance());
}
