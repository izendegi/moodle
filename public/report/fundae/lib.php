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
 * @package report_fundae
 * @author 3iPunt <https://www.tresipunt.com/>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 3iPunt <https://www.tresipunt.com/>
 */

defined('MOODLE_INTERNAL') || die();

/**
 * @param navigation_node $parentnode
 * @param stdClass $course
 * @param context_course $context
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 */
function report_fundae_extend_navigation_course(navigation_node $parentnode, stdClass $course, context_course $context) {
    // need moodle/site:viewreports capability for this method to be executed.
    global $USER, $DB;
    $url = new moodle_url("/report/fundae/coursereport.php", ['courseid' => $course->id]);
    $name = get_string('pluginname', 'report_fundae');
    $icon = new pix_icon('icono', $name, 'report_fundae');
    $coursecontext = context_course::instance($course->id);
    $roles = get_user_roles($coursecontext, $USER->id);
    $addnode = false;
    if (has_capability('report/fundae:view', $coursecontext)) {
        if ((int)get_config('report_fundae', 'teachersseereports') === 1) {
            foreach ($roles as $role) {
                $roledata = $DB->get_record('role', ['id' => $role->roleid], 'archetype');
                if ($roledata->archetype === 'manager' || $roledata->archetype === 'editingteacher' || $roledata->archetype === 'teacher') {
                    $addnode = true;
                    break;
                }
            }
        }
        if ((int)get_config('report_fundae', 'studentsseereports') === 1) {
            foreach ($roles as $role) {
                $roledata = $DB->get_record('role', ['id' => $role->roleid], 'archetype');
                if ($roledata->archetype === 'student' || $roledata->archetype === 'guest' || $roledata->archetype === 'user') {
                    $addnode = true;
                    break;
                }
            }
        }
    }

    if (has_capability('report/fundae:manage', context_system::instance())) {
        $addnode = true;
    }

    if ($addnode) {
        $parentnode->add($name, $url, navigation_node::TYPE_SETTING, null, null, $icon);
    }
}
