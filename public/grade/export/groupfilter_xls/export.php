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

// Project implemented by the &quot;Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU&quot;.
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.
/**
 * Display information about all the gradeexport_groupfilter_xls modules in the requested course. *
 * @package gradeexport_groupfilter_xls
 * @copyright 2023 Proyecto UNIMOODLE
 * @author UNIMOODLE Group (Coordinator) &lt;direccion.area.estrategia.digital@uva.es&gt;
 * @author Miguel Gutiérrez (UPCnet) &lt;miguel.gutierrez.jariod@upcnet.es&gt;
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace gradeexport_groupfilter_xls;

require_once('../../../config.php');
require_once($CFG->dirroot . '/grade/export/lib.php');
require_once('grade_export_groupfilter_xls.php');

$id = required_param('id', PARAM_INT); // Course id.
$PAGE->set_url('/grade/export/groupfilter_xls/export.php', ['id' => $id]);

if (!$course = $DB->get_record('course', ['id' => $id])) {
    throw new \moodle_exception('invalidcourseid');
}

require_login($course);
$context = \context_course::instance($id);
$groupid = groups_get_course_group($course, true);

require_capability('moodle/grade:export', $context);
require_capability('gradeexport/groupfilter_xls:view', $context);

// We need to call this method here before any print otherwise the menu won't display.
// If you use this method without this check, will break the direct grade exporting (without publishing).
$key = optional_param('key', '', PARAM_RAW);
if (!empty($CFG->gradepublishing) && !empty($key)) {
    $actionbar = new \core_grades\output\export_publish_action_bar($context, 'groupfilter_xls');
    print_grade_page_head(
        $COURSE->id,
        'export',
        'groupfilter_xls',
        get_string('exportto', 'grades') . ' ' . get_string('pluginname', 'gradeexport_groupfilter_xls'),
        false,
        false,
        true,
        null,
        null,
        null,
        $actionbar
    );
}

if (groups_get_course_groupmode($COURSE) == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $context)) {
    if (!groups_is_member($groupid, $USER->id)) {
        throw new \moodle_exception('cannotaccessgroup', 'grades');
    }
}

$mform = new grade_export_form(
    null,
    ['publishing' => true, 'simpleui' => true, 'multipledisplaytypes' => true]
);
// Form processing and displaying is done here.
if ($mform->is_cancelled()) {
    // If there is a cancel element on the form, and it was pressed,
    // then the `is_cancelled()` function will return true.
    // You can handle the cancel operation here.
    redirect(
        $CFG->wwwroot . "/grade/export/groupfilter_xls/index.php?id=" . $id,
        "Form Canceled",
        null,
        \core\output\notification::NOTIFY_INFO
    );
} else if ($fromform = $mform->get_data()) {
    // When the form is submitted, and the data is successfully validated,
    // the `get_data()` function will return the data posted in the form.
    $formdata = $mform->get_data();
    $export = new grade_export_groupfilter_xls($course, $groupid, $formdata);
} else {
    // This branch is executed if the form is submitted but the data doesn't
    // validate and the form should be redisplayed or on the first display of the form.
    // Display the form.
    $mform->display();
}

// If the gradepublishing is enabled and user key is selected print the grade publishing link.
if (!empty($CFG->gradepublishing) && !empty($key)) {
    \groups_print_course_menu($course, 'index.php?id=' . $id);
    echo $export->get_grade_publishing_url();
    echo $OUTPUT->footer();
} else {
    $event = \gradeexport_groupfilter_xls\event\grade_exported::create(['context' => $context]);
    $event->trigger();
    $export->print_grades();
}
