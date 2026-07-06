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
 * SMOWL upgrade.
 *
 * @package     block_smowl
 * @author      Smowltech <info@smowltech.com>
 * @copyright   Smiley Owl Tech S.L.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Handles upgrading instances of this block.
 *
 * @param int $oldversion
 * @param object $block
 * @return boolean
 */
function xmldb_block_smowl_upgrade($oldversion, $block) {
    global $CFG, $DB;
    if ($oldversion < 2021110203) {
        require_once($CFG->dirroot . "/blocks/smowl/classes/smowl_connection.php");

        $name = 'urlstudentview';
        $default = block_smowl_connection::get_default_urlstudentview();
        set_config($name, $default, 'block_smowl');

        upgrade_block_savepoint(true, 2021110203, 'smowl');
    }

    if ($oldversion < 2022112300) {
        require_once($CFG->dirroot . "/blocks/smowl/classes/smowl_connection.php");

        // API URL and functions.
        $name = 'urlsmowlapi';
        $default = block_smowl_connection::get_default_urlsmowlapi();
        set_config($name, $default, 'block_smowl');

        $name = 'apilmssettings';
        $default = block_smowl_connection::get_default_apilmssettings();
        set_config($name, $default, 'block_smowl');

        upgrade_block_savepoint(true, 2022112300, 'smowl');
    }
    if ($oldversion < 2022120700) {
        $name = 'accesscontrol';
        set_config($name, 0, 'block_smowl');

        upgrade_block_savepoint(true, 2022120700, 'smowl');
    }
    if ($oldversion < 2023070700) {
        require_once($CFG->dirroot . "/blocks/smowl/classes/smowl_connection.php");

        // API URL and functions redefine.
        $name = 'urlsmowlapi';
        $default = block_smowl_connection::get_default_urlsmowlapi();
        set_config($name, $default, 'block_smowl');

        $name = 'apilmssettings';
        $default = block_smowl_connection::get_default_apilmssettings();
        set_config($name, $default, 'block_smowl');

        // Update apiconfigclient.
        $name = 'apiconfigclient';
        $default = block_smowl_connection::get_default_apiconfigclient();
        set_config($name, $default, 'block_smowl');

        // Upgrade, por defecto configuración manual.
        $name = 'typeconfig';
        set_config($name, 2, 'block_smowl');

        $name = 'lmsid';
        $moodle = 1;
        $olms = 3;
        set_config($name, $moodle, 'block_smowl');

        upgrade_block_savepoint(true, 2023070700, 'smowl');
    }
    if ($oldversion < 2023110700) {
        require_once($CFG->dirroot . "/blocks/smowl/classes/smowl_connection.php");

        $name = 'apilmssettingscustomer';
        $default = block_smowl_connection::get_default_apilmssettingscustomer();
        set_config($name, $default, 'block_smowl');

        upgrade_block_savepoint(true, 2023110700, 'smowl');
    }
    if ($oldversion < 2023121700) {
        require_once($CFG->dirroot . "/blocks/smowl/classes/smowl_connection.php");

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

        require_once($CFG->dirroot . "/blocks/smowl/classes/smowl_connection.php");

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

        upgrade_block_savepoint(true, 2023121700, 'smowl');
    }

    if ($oldversion < 2024011100) {
        // Deletting the settings.
        $names = [];
        $names[] = 'urlusercheckregistry';
        $names[] = 'urluserregistry';
        $names[] = 'showregister';
        $names[] = 'showcm';
        $names[] = 'urldownloadcm';
        $names[] = 'showcheckcam';
        $names[] = 'urlwebcamcheck';
        $names[] = 'showfaq';
        $names[] = 'urlfaq';
        $names[] = 'teachersviewregistry';
        $names[] = 'urlviewregistry';
        $names[] = 'downloadimages';
        $names[] = 'urlviewactivities';

        $default = null;
        foreach ($names as $name) {
            set_config($name, $default, 'block_smowl');
        }
        upgrade_block_savepoint(true, 2024011100, 'smowl');
    }

    if ($oldversion < 2024011500) {
        require_once($CFG->dirroot . "/blocks/smowl/classes/smowl_connection.php");
        $name = 'apiaddactivity';
        $default = block_smowl_connection::get_default_apiaddactivity();
        set_config($name, $default, 'block_smowl');

        $name = 'apimodifyactivity';
        $default = block_smowl_connection::get_default_apimodifyactivity();
        set_config($name, $default, 'block_smowl');

        $name = 'urlpostinstances';
        set_config($name, null, 'block_smowl');

        upgrade_block_savepoint(true, 2024011500, 'smowl');
    }

    if ($oldversion < 2024020301) {
            // Update services in moodle.
        $name = 'ltitypeid';
        set_config($name, null, 'block_smowl');
        require_once($CFG->dirroot . '/lib/upgradelib.php');
        external_update_descriptions('block_smowl');
        // external_update_services();
        require_once($CFG->dirroot . '/blocks/smowl/lib.php');
        block_smowl_lti_clean_types();
        $ltitool = block_smowl_lti_create_tool();
        block_smowl_api_send_lti_ws_config($ltitool);

        upgrade_block_savepoint(true, 2024020301, 'smowl');
    }

    if ($oldversion < 2024030100) {
        // Update services in moodle.
        require_once($CFG->dirroot . '/lib/upgradelib.php');
        external_update_descriptions('block_smowl');
        upgrade_block_savepoint(true, 2024030100, 'smowl');
    }
    if ($oldversion < 2024111302) {
        require_once($CFG->dirroot . "/blocks/smowl/classes/smowl_connection.php");

        $name = 'urlstudentview';
        $default = block_smowl_connection::get_default_urlstudentview();
        set_config($name, $default, 'block_smowl');

        upgrade_block_savepoint(true, 2024111302, 'smowl');
    }

    if ($oldversion < 2025061900) {
        require_once($CFG->dirroot . "/blocks/smowl/classes/smowl_connection.php");

        $roleid = $DB->get_field('role', 'id', ['shortname' => 'smowlwsrole']);
        if(!$roleid){
            $roleid = create_role(
                'SMOWL WebService User',
                'smowlwsrole',
                'Role for web service access to SMOWL-related endpoints'
            );

            assign_capability('block/smowl:manageactivities', CAP_ALLOW, $roleid, context_system::instance());
            assign_capability('moodle/course:viewhiddencourses', CAP_ALLOW, $roleid, context_system::instance());
            assign_capability('moodle/user:viewdetails', CAP_ALLOW, $roleid, context_system::instance());
            assign_capability('moodle/user:viewhiddendetails', CAP_ALLOW, $roleid, context_system::instance());
            assign_capability('moodle/user:update', CAP_ALLOW, $roleid, context_system::instance());
            assign_capability('moodle/course:useremail', CAP_ALLOW, $roleid, context_system::instance());
            assign_capability('moodle/course:view', CAP_ALLOW, $roleid, context_system::instance());
            assign_capability('moodle/course:update', CAP_ALLOW, $roleid, context_system::instance());
            assign_capability('moodle/course:viewparticipants', CAP_ALLOW, $roleid, context_system::instance());
            assign_capability('moodle/site:accessallgroups', CAP_ALLOW, $roleid, context_system::instance());
            assign_capability('mod/quiz:view', CAP_ALLOW, $roleid, context_system::instance());
        }

        $conditions = [
            'username' => 'smowlws',
            'mnethostid' => $CFG->mnet_localhost_id,
            'deleted' => 0,
        ];
        $user = $DB->get_record('user', $conditions);
        if (!empty($user) && isset($user->id)) {
            $userid = $user->id;
            role_assign($roleid, $userid, context_system::instance());
        }

        upgrade_block_savepoint(true, 2025061900, 'smowl');
    }

    if ($oldversion < 2025081900) {
        require_once($CFG->dirroot . "/blocks/smowl/classes/smowl_connection.php");

        $roleid = $DB->get_field('role', 'id', ['shortname' => 'smowlwsrole']);

        if ($roleid) {
            assign_capability('webservice/rest:use', CAP_ALLOW, $roleid, context_system::instance());
            assign_capability('moodle/site:viewparticipants', CAP_ALLOW, $roleid, context_system::instance());
        }

        upgrade_block_savepoint(true, 2025081900, 'smowl');
    }
    if ($oldversion < 2025102300) {
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'smowlwsrole']);

        if ($roleid) {
            assign_capability('mod/workshop:view', CAP_ALLOW, $roleid, context_system::instance());
            assign_capability('mod/assign:view', CAP_ALLOW, $roleid, context_system::instance());

            $roleContextCapabilities = role_context_capabilities($roleid, context_system::instance());

            if (isset($roleContextCapabilities['moodle/user:update']) && $roleContextCapabilities['moodle/user:update'] == CAP_ALLOW) {
                unassign_capability('moodle/user:update', $roleid, context_system::instance());
            }

            if (isset($roleContextCapabilities['moodle/course:update']) && $roleContextCapabilities['moodle/course:update'] == CAP_ALLOW) {
                unassign_capability('moodle/course:update', $roleid, context_system::instance());
            }

            if (isset($roleContextCapabilities['moodle/user:viewhiddendetails']) && $roleContextCapabilities['moodle/user:viewhiddendetails'] == CAP_ALLOW) {
                unassign_capability('moodle/user:viewhiddendetails', $roleid, context_system::instance());
            }
        }

        upgrade_block_savepoint(true, 2025102300, 'smowl');
    }

    $smowlsettings = block_smowl_prepare_lms_settings();
    block_smowl_send_lms_settings($smowlsettings);

    return true;
}