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

define('NO_MOODLE_COOKIES', true); // Session not used here.
require_once('../../../config.php');
require_once($CFG->dirroot.'/grade/export/groupfilter_xls/grade_export_groupfilter_xls.php');

$id                 = required_param('id', PARAM_INT);
$groupid            = optional_param('groupid', 0, PARAM_INT);
$itemids            = required_param('itemids', PARAM_RAW);
$exportfeedback     = optional_param('export_feedback', 0, PARAM_BOOL);
$displaytype        = optional_param('displaytype', $CFG->grade_export_displaytype, PARAM_RAW);
$decimalpoints      = optional_param('decimalpoints', $CFG->grade_export_decimalpoints, PARAM_INT);
$onlyactive         = optional_param('export_onlyactive', 0, PARAM_BOOL);

if (!$course = $DB->get_record('course', ['id' => $id])) {
    throw new \moodle_exception('invalidcourseid');
}

require_user_key_login('grade/export', $id); // We want different keys for each course.

if (empty($CFG->gradepublishing)) {
    throw new \moodle_exception('gradepubdisable');
}

$context = context_course::instance($id);
require_capability('moodle/grade:export', $context);
require_capability('gradeexport/groupfilter_xls:view', $context);
require_capability('gradeexport/groupfilter_xls:publish', $context);

if (!groups_group_visible($groupid, $COURSE)) {
    throw new \moodle_exception('cannotaccessgroup', 'grades');
}

// Get all url parameters and create an object to simulate a form submission.
$formdata = grade_export::export_bulk_export_data($id, $itemids, $exportfeedback, $onlyactive, $displaytype,
        $decimalpoints);

$export = new gradeexport_groupfilter_xls\grade_export_groupfilter_xls($course, $groupid, $formdata);
$export->print_grades();


