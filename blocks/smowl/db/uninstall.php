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
function xmldb_block_smowl_uninstall() {
    global $DB, $CFG;
    require_once($CFG->dirroot.'/blocks/smowl/classes/smowl_connection.php');
    require_once($CFG->dirroot . '/blocks/smowl/lib.php');

    $smowlsettings = block_smowl_prepare_lms_settings();
    $smowlsettings->unisntalled = 1;
    block_smowl_send_lms_settings($smowlsettings);

    // Delete LTI Tool.
    $urlltitool = block_smowl_connection::get_urlsmowlltitool();
    $urlinit = $urlltitool . block_smowl_connection::get_ltitoolinit();

    $comparetext = $DB->sql_compare_text('baseurl') . ' = ' . $DB->sql_compare_text(':baseurl');
    $sql = 'SELECT id FROM {lti_types} WHERE ' . $comparetext;

    $tools = $DB->get_records_sql($sql, ['baseurl' => $urlinit]);

    $deltypes = [];
    if (!empty($tools)) {
        require_once($CFG->dirroot.'/mod/lti/locallib.php');
        foreach ($tools as $tool) {
            $deltypes[] = $tool->id;
            lti_delete_type($tool->id);
        }
    }

    // Delete LTI Activities.
    if (!empty($deltypes)) {
        $deltypesstr = implode(',', $deltypes);
        $modulename = 'lti';
        $sql = "SELECT cm.*
            FROM {course_modules} cm
            JOIN {modules} md ON md.id = cm.module
            JOIN {lti} m ON m.id = cm.instance AND m.typeid IN (:deltypes)
            WHERE md.name = :modulename";
        $params = ['modulename' => $modulename, 'deltypes' => $deltypesstr];
        $ltis = $DB->get_records_sql($sql, $params, IGNORE_MISSING);
        if (!empty($ltis)) {
            require_once("$CFG->dirroot/course/lib.php");
            foreach ($ltis as $lti) {
                course_delete_module($lti->id);
            }
        }
    }

    $conditions = [
        'username' => 'smowlws',
        'mnethostid' => $CFG->mnet_localhost_id,
        'deleted' => 0,
    ];
    $user = $DB->get_record('user', $conditions);
    if (!empty($user) && isset($user->id)) {
        $userid = $user->id;
        $isadmnin = false;
        $admins = [];
        foreach (explode(',', $CFG->siteadmins) as $admin) {
            $admin = (int)$admin;
            if ($admin == $userid) {
                $isadmnin = true;
            } else {
                $admins[$admin] = $admin;
            }
        }
        if ($isadmnin) {
            $logstringold = $CFG->siteadmins;
            $logstringnew = implode(', ', $admins);
            set_config('siteadmins', implode(',', $admins));
            add_to_config_log('siteadmins', $logstringold, $logstringnew, 'core');
        }
        require_once($CFG->dirroot . '/user/lib.php');
        user_delete_user($user);
    }

    $roleid = $DB->get_field('role', 'id', ['shortname' => 'smowlwsrole']);
    if ($roleid) {
        delete_role($roleid);
    }
}
