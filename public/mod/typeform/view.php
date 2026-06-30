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
 * Typeform module main user interface
 *
 * @package    mod_typeform
 * @copyright  2026 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
global $DB, $PAGE, $OUTPUT, $CFG;

require_once("$CFG->dirroot/mod/typeform/lib.php");
require_once($CFG->libdir . '/completionlib.php');

$id       = optional_param('id', 0, PARAM_INT);        // Course module ID
$u        = optional_param('u', 0, PARAM_INT);         // Typeform instance id

if ($u) {  // Two ways to specify the module
    $typeform = $DB->get_record('typeform', ['id' => $u], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('typeform', $typeform->id, $typeform->course, false, MUST_EXIST);
} else {
    $cm = get_coursemodule_from_id('typeform', $id, 0, false, MUST_EXIST);
    $typeform = $DB->get_record('typeform', ['id' => $cm->instance], '*', MUST_EXIST);
}

$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/typeform:view', $context);

// Completion and trigger events.
typeform_view($typeform, $course, $cm, $context);

$url = new \core\url('/mod/typeform/view.php', ['id' => $cm->id]);
$PAGE->set_url($url);
$PAGE->set_title($typeform->name);
$PAGE->set_heading($course->fullname);

// Check if user has already completed the form
global $USER;
$completion = new completion_info($course);
$completiondata = $completion->get_data($cm, false, $USER->id);
$alreadycompleted = ($completiondata->completionstate == COMPLETION_COMPLETE);

// Output starts here
echo $OUTPUT->header();
echo $OUTPUT->heading($typeform->name);

// Display description if available
if (!empty($typeform->intro)) {
    echo $OUTPUT->box(format_module_intro('typeform', $typeform, $cm->id), 'generalbox mod_introbox', 'typeformintro');
}

// Display completion status
if ($alreadycompleted) {
    echo $OUTPUT->notification(get_string('alreadycompleted', 'typeform'), 'notifysuccess');
}

// Embed Typeform
$typeformid = $typeform->typeformid;
$includestudentcode = !empty($typeform->includestudentcode);
$typeformjslink = get_config('typeform', 'typeformjslink');
$typeformdomain = get_config('typeform', 'typeformdomain');

// Load the Typeform embed JavaScript module.
// The student_code value is always anonymised; the setting only controls whether it is included in the URL.
$PAGE->requires->js_call_amd('mod_typeform/embed', 'init', [
    [
        'typeformid' => $typeformid,
        'cmid' => $cm->id,
        'includestudentcode' => $includestudentcode,
        'alreadycompleted' => $alreadycompleted,
        'typeformjslink' => $typeformjslink,
        'typeformdomain' => $typeformdomain,
        'studentCode' => hash('sha256', $USER->id),
    ],
]);

// Container for the embedded form
echo html_writer::start_div('typeform-container', ['id' => 'typeform-embed-container']);
echo html_writer::end_div();
$content = get_string('reloadpage', 'mod_typeform');
$content .= html_writer::link($url, get_string('clicktoreload', 'mod_typeform'));
echo html_writer::tag('div', $content, ['class' => 'mt-5']);

// Completion message (hidden by default, shown via JS)
echo html_writer::start_div('typeform-completion-message', ['id' => 'typeform-completion-message', 'style' => 'display: none;']);
echo $OUTPUT->notification(get_string('formcompleted', 'typeform'), 'notifysuccess');
echo html_writer::end_div();

echo $OUTPUT->footer();
