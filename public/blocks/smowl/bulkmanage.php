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
 * Admin Manage SMOWL file.
 *
 * @package     block_smowl
 * @author      Smowltech <info@smowltech.com>
 * @copyright   Smiley Owl Tech S.L.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/blocks/smowl/lib.php');

// Optional params.
$action = optional_param('action', 'bulkactive', PARAM_ALPHA);

require_login();

$context = context_course::instance(SITEID);
$PAGE->set_context($context);

$canmanagebulkactions = block_smowl_user_can_managebulkactions($context);
if (!$canmanagebulkactions) {
    $message = get_string('notviewmanagebuklpermissions', 'block_smowl');
    redirect($CFG->wwwroot.'/?redirect=0', $message, null, \core\output\notification::NOTIFY_ERROR);
}

// Entity setting setted.
$entity = get_config('block_smowl', 'entity');
block_smowl_check_entity_navigation();

// Block instanced.
block_smowl_check_only_once_course(SITEID);
$blockinstance = block_smowl_check_course_block();
if (!$blockinstance) {
    $message = get_string('notinstancedblockincourse', 'block_smowl');
    redirect($CFG->wwwroot.'/?redirect=0', $message, null, \core\output\notification::NOTIFY_ERROR);
}

// Navbar & header.
$urlparams = ['action' => $action];

$PAGE->set_url('/blocks/smowl/bulkmanage.php', $urlparams);

$PAGE->set_heading($SITE->fullname);
$PAGE->set_title(get_string('pluginname', 'block_smowl')
    .': '. get_string($action, 'block_smowl'));

$PAGE->navbar->add(get_string('pluginname', 'block_smowl'));
$PAGE->navbar->add(get_string($action, 'block_smowl'), new moodle_url('/blocks/smowl/bulkmanage.php', $urlparams));

$PAGE->set_pagelayout('admin');

$renderer = $PAGE->get_renderer('block_smowl');

// Labels.
$row = [];
$urlbulkactive = new moodle_url('/blocks/smowl/bulkmanage.php', ['action' => 'bulkactive']);
$strbulkactive = get_string('bulkactive', 'block_smowl');
$row[] = new tabobject('bulkactive', $urlbulkactive, $strbulkactive);

$urlbulkgroups = new moodle_url('/blocks/smowl/bulkmanage.php', ['action' => 'bulkgroups']);
$strbulkgroups = get_string('bulkgroups', 'block_smowl');
$row[] = new tabobject('bulkgroups', $urlbulkgroups, $strbulkgroups);

// Action bulkactive.
if ($action == 'bulkactive') {
    require_once($CFG->dirroot.'/blocks/smowl/classes/bulkactive_form.php');

    $category = optional_param('category', -1, PARAM_INT);
    $mod = optional_param('mod', '', PARAM_ALPHA);
    $activityname = optional_param('activityname', '', PARAM_RAW_TRIMMED);
    $formparams = [];

    if ($category != -1) {
        $formparams = [
            'category' => $category,
            'mod' => $mod,
            'activityname' => $activityname,
        ];
    }

    $mform = new bulkactive_form(null, $formparams, 'post');

    if ($mform->is_cancelled()) {
        redirect($CFG->wwwroot.'/?redirect=0');

    } else if ($fromform = $mform->get_data()) {
        $instances = [];
        if (isset($fromform->submitbutton) &&
                $fromform->submitbutton == get_string('searchactivities', 'block_smowl')) {
            $urlparams['category'] = $category;
            $urlparams['mod'] = $mod;
            $urlparams['activityname'] = $activityname;
            redirect(new moodle_url($CFG->wwwroot . '/blocks/smowl/bulkmanage.php', $urlparams));
        }

        $blockinstances = block_smowl_get_instances();
        foreach ($fromform as $instanceid => $instance) {
            if (stripos($instanceid, "mod-") === 0) {
                if ($instance) {
                    $instances[$instanceid] = 1;
                } else {
                    $instances[$instanceid] = 0;
                }
            }
        }
        // Check pre-process data and send data to SMOWL.
        block_smowl_bulk_check_instances($instances, $blockinstances);
        $message = get_string('bulkactiveupdate', 'block_smowl');
        redirect($CFG->wwwroot.'/?redirect=0', $message, null, \core\output\notification::NOTIFY_INFO);
    }
    echo $renderer->header_manage($row, $action);
    if (!block_smowl_check_entity()) {
        echo $OUTPUT->notification(get_string('noticeemptysmowlconfig', 'block_smowl'), 'danger');
    } else {
        $mform->display();
    }
}

// Action bulkgroups.
if ($action == 'bulkgroups') {

    require_once($CFG->dirroot.'/blocks/smowl/classes/bulkgroups_form.php');
    $category = optional_param('category', -1, PARAM_INT);
    $groupname = optional_param('groupname', '', PARAM_ALPHANUM);
    $formparams = [];

    if ($category != -1) {
        $formparams = [
            'category' => $category,
            'groupname' => $groupname,
        ];
    }

    $mform = new bulkgroups_form(null, $formparams, 'post');

    if ($mform->is_cancelled()) {
        // Cancel.
        redirect($CFG->wwwroot.'/?redirect=0');

    } else if ($fromform = $mform->get_data()) {
        if (isset($fromform->submitbutton) &&
                $fromform->submitbutton == get_string('searchgroups', 'block_smowl')) {
            $urlparams['category'] = $category;
            $urlparams['groupname'] = $groupname;
            redirect(new moodle_url($CFG->wwwroot . '/blocks/smowl/bulkmanage.php', $urlparams));
        }
        $selectedgroups = [];
        foreach ($fromform as $element => $value) {
            if ($value && strpos($element, 'group-') === 0) {
                $selectedgroups[] = substr($element, 6);
            }
        }
        if (empty($selectedgroups)) {
            $message = get_string('managegroupsupdate', 'block_smowl');
            redirect($CFG->wwwroot.'/?redirect=0', $message, null, \core\output\notification::NOTIFY_INFO);
        }
        if ($fromform->category > 0) {
            // Selected category and sub categories.
            $coursecat = core_course_category::get($fromform->category);
            $courses = $coursecat->get_courses(['recursive' => $coursecat->id]);
        } else {
            $sql = "category<>0";
            $courses = $DB->get_records_select('course', $sql, [], 'id', 'id, fullname');
        }

        $groups = block_smowl_bulkgroups_selected_groups($courses, $selectedgroups);

        block_smowl_bulkgroups_save_strict_configdata($groups);

        $message = get_string('managegroupsupdate', 'block_smowl');
        redirect($CFG->wwwroot.'/?redirect=0', $message, null, \core\output\notification::NOTIFY_INFO);
    }

    if (!block_smowl_check_entity()) {
        echo $OUTPUT->notification(get_string('noticeemptysmowlconfig', 'block_smowl'), 'danger');
    } else {
        echo $renderer->header_manage($row, $action);
        $mform->display();
    }
}

// Footer.
 echo $OUTPUT->footer();
