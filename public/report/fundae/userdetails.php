<?php

use report_fundae\output\activities_report_table;

require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT, $USER;
require_once($CFG->dirroot . '/report/fundae/locallib.php');

$courseid = required_param('courseid', PARAM_INT);
$userid = required_param('userid', PARAM_INT);
$download = optional_param('download', '', PARAM_ALPHA);
$generate = optional_param('generate', '', PARAM_ALPHA);

// User authentication.
$context = context_system::instance();
$coursecontext = context_course::instance($courseid);
require_login($courseid);
require_capability('report/fundae:view', $coursecontext);

if ((int)$userid !== (int)$USER->id) {
    require_capability('moodle/user:viewdetails', $coursecontext);
}

// Page setup.
$reporturl = new moodle_url('/report/fundae/userdetails.php', ['courseid' => $courseid, 'userid' => $userid]);
$PAGE->set_url($reporturl);
$PAGE->set_title(get_string('pluginname', 'report_fundae'));
$PAGE->set_heading(get_string('pluginname', 'report_fundae'));
$PAGE->set_cacheable(false);
$PAGE->navbar->add(get_string('pluginname', 'report_fundae'));
$PAGE->navbar->add(get_string('details', 'report_fundae'));
$PAGE->navbar->add(format_string(get_course_display_name_for_list(get_course($courseid)), true, ['context' => $context]));
$PAGE->navbar->add(fullname(core_user::get_user($userid)));

// Page buttons.
$PAGE->set_button($PAGE->button);

$report = new activities_report_table($courseid, $userid, $download);

// If downloading report, print report and early exit.
if ($report->is_downloading()) {
    $report->out(0, false);
    exit;
}

warn_if_cron_disabled();

// Print report table.
echo $OUTPUT->header();
$report->out(0, true);
echo $OUTPUT->footer();
