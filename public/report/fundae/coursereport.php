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

use report_fundae\output\course_report_table;
use report_fundae\select_course_form;

require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $USER, $DB;
require_once($CFG->dirroot . '/report/fundae/locallib.php');

$courseid = required_param('courseid', PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$download = optional_param('download', '', PARAM_ALPHA);
$userid = optional_param('userid', 0, PARAM_INT);


// User authentication.
$context = context_system::instance();
$coursecontext = context_course::instance($courseid);
require_login($courseid);
require_capability('report/fundae:view', $coursecontext);

// Page setup.
$urlparams = ['courseid' => $courseid, 'perpage' => $perpage, 'page' => $page];
$reporturl = new moodle_url('/report/fundae/coursereport.php', $urlparams);
$PAGE->navigation->override_active_url($reporturl);
$PAGE->set_url('/report/fundae/coursereport.php', $urlparams);
$PAGE->set_title(get_string('pluginname', 'report_fundae'));
$PAGE->set_heading(get_string('pluginname', 'report_fundae'));
$PAGE->set_cacheable(false);

// Page buttons.
$PAGE->set_button($PAGE->button);
$isstudent = true;
if (has_capability('report/fundae:manage', $context)) {
    if ($userid !== 0) {
        $perpage = 0;
        $page = 0;
    }
    $report = new course_report_table($courseid, $perpage, $page, $download, false, $userid);
} else {
    $roles = get_user_roles($coursecontext, $USER->id);
    foreach ($roles as $role) {
        $roledata = $DB->get_record('role', ['id' => $role->roleid], 'archetype');
        if ($roledata->archetype === 'manager' || $roledata->archetype === 'editingteacher' || $roledata->archetype === 'teacher') {
            $isstudent = false;
        }
    }
    $report = new course_report_table($courseid, $perpage, $page, $download, $isstudent);
}

// If downloading report, print report and early exit.
if ($report->is_downloading()) {
    $report->out(0, false);
    exit;
}
warn_if_cron_disabled();

// Print report table.
echo $OUTPUT->header();

if ($isstudent === false || has_capability('report/fundae:manage', $context)) {
    $filters = new select_course_form(null, null, 'GET');
    $data = ['perpage' => $perpage];
    if ($courseid > 0) {
        $data['courseid'] = $courseid;
    }
    if ($userid > 0) {
        $data['userid'] = $userid;
    }
    $filters->set_data($data);
    $filters->display();
}

$report->out($perpage, true);
echo $OUTPUT->footer();
