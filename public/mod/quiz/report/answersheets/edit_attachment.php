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
 * Edit attachment for essay question
 *
 * @package   quiz_answersheets
 * @copyright 2026 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use quiz_answersheets\edit_attachment_form;
use mod_quiz\quiz_attempt;
use quiz_answersheets\utils;

require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->dirroot . '/repository/lib.php');

global $DB, $USER, $OUTPUT, $PAGE;

$attemptid = required_param('attemptid', PARAM_INT);
$questionid = optional_param('questionid', null, PARAM_INT);
$slot = optional_param('slot', null, PARAM_INT);
$returnurl = required_param('returnurl', PARAM_URL);

$attemptobj = quiz_attempt::create($attemptid);
$context = context_module::instance($attemptobj->get_cmid());
$course = $attemptobj->get_course();
require_login($course, false, $attemptobj->get_cm());
require_capability('quiz/answersheets:submitresponses', $context);
$PAGE->set_url(new moodle_url('/mod/quiz/report/answersheets/edit_attachment.php', [
    'attemptid' => $attemptid,
    'questionid' => $questionid,
    'slot' => $slot,
    'returnurl' => $returnurl,
]));
$qa = $attemptobj->get_question_attempt($slot);
$currentquestion = $qa->get_question();
$displayoption = $attemptobj->get_display_options(true);
// Prepare filemanager options.
$filemanageroptions = [
    'subdirs' => 0,
    'maxbytes' => $currentquestion->maxbytes ?: 0,
    'maxfiles' => $currentquestion->attachments ?: 1,
    'accepted_types' => $currentquestion->filetypeslist,
    'context' => $context,
    'return_types' => FILE_INTERNAL | FILE_CONTROLLED_LINK,
];

// Prepare draft itemid for attachments.
$itemid = $qa->prepare_response_files_draft_itemid('attachments', $context->id);
$record = new \stdClass();
$record->attachments = $itemid;

$userid = $attemptobj->get_userid();
$user = core_user::get_user($userid);
$studentname = utils::get_user_details($user, $context, ['fullname', 'username', 'idnumber']);

// Pass options and data to the form.
$mform = new edit_attachment_form(null, [
    'studentname' => $studentname,
    'filemanageroptions' => $filemanageroptions,
    'returnurl' => $returnurl,
    'attemptid' => $attemptid,
    'questionid' => $questionid,
    'slot' => $slot,
]);
$mform->set_data($record);

$qubaid = $attemptobj->get_uniqueid();
$quba = question_engine::load_questions_usage_by_activity($qubaid);
if ($mform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $mform->get_data()) {
    $qa = $attemptobj->get_question_attempt($slot);
    $draftitemid = $data->attachments;
    $contextid = $context->id;
    $fs = get_file_storage();
    // Collect draft file information (for comment + summary).
    $fileinfo = [];

    $draftfiles = $fs->get_area_files(
        context_user::instance($USER->id)->id,
        'user',
        'draft',
        $draftitemid,
        'id',
        false
    );
    foreach ($draftfiles as $file) {
        $fileinfo[] = $file->get_filename() .
            ' (' . display_size($file->get_filesize()) . ')';
    }
    $comment = get_string(
        'essay_edited_attachment',
        'quiz_answersheets',
        implode(', ', $fileinfo)
    );
    // Ensure a step exists with the "attachments" variable.
    $attachmentstep = $qa->get_last_step_with_qt_var('attachments');
    if ($attachmentstep && $attachmentstep->get_id()) {
        $stepid = $attachmentstep->get_id();
    } else {
        // Hack: manually insert attachments qt_var so renderer detects files.
        // This is not recommend, but it is the only way.
        $stepid = $qa->get_last_step()->get_id();
        $record = (object)[
            'attemptstepid' => $stepid,
            'name' => 'attachments',
            'value' => 1,
        ];
        $DB->insert_record('question_attempt_step_data', $record);
    }
    // Save comment into response history.
    $transaction = $DB->start_delegated_transaction();
    $quba->process_action($slot, ['-comment' => $comment]);
    question_engine::save_questions_usage_by_activity($quba);

    // Reload QA after process_action (may create a new step).
    $qa = $quba->get_question_attempt($slot);

    if ($stepid) {
        // Delete existing attachment files.
        $fs->delete_area_files(
            $contextid,
            'question',
            'response_attachments',
            $stepid
        );
    }

    file_save_draft_area_files(
        $draftitemid,
        $contextid,
        'question',
        'response_attachments',
        $stepid,
        $filemanageroptions
    );
    $transaction->allow_commit();

    // Rebuild response summary (because attachments were inserted manually).
    $response = $qa->get_last_qt_data();
    $summary = '';
    if (!empty($response['answer'])) {
        $summary .= question_utils::to_plain_text(
            $response['answer'],
            $response['answerformat'],
            ['para' => false]
        );
    }

    if (!empty($fileinfo)) {
        $summary .= get_string(
            'attachedfiles',
            'qtype_essay',
            implode(', ', $fileinfo)
        );
    }

    $DB->set_field(
        'question_attempts',
        'responsesummary',
        $summary,
        ['id' => $qa->get_database_id()]
    );
    redirect($returnurl, get_string('essay_responseeditedsuccess', 'quiz_answersheets'));
}
$PAGE->set_title(get_string('essay_editing_student_responses', 'quiz_answersheets'));

utils::set_page_navigation(
    get_string('essay_editing_student_responses', 'quiz_answersheets'),
    ['text' => get_string('review_sheet_label', 'quiz_answersheets'), 'url' => (new moodle_url($returnurl))->out()]
);
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('essay_editing_student_responses', 'quiz_answersheets'));

$questionnumber = $attemptobj->get_question_number($slot);
$displayoption = $attemptobj->get_display_options(true);
$displayoption->flags = question_display_options::HIDDEN;
$displayoption->userinfoinhistory = question_display_options::HIDDEN;
$displayoption->hide_all_feedback();
$displayoption->context = $context;

$qoutput = $PAGE->get_renderer('quiz_answersheets', 'core_question_override');
$essayoutput = $PAGE->get_renderer('qtype_essay');
$qtoutput = utils::get_question_renderer($PAGE, $qa);
$behaviouroutput = $PAGE->get_renderer(get_class($qa->get_behaviour()));
$html = $qoutput->question($qa, $behaviouroutput, $qtoutput, $displayoption, $questionnumber);

$dom = new DOMDocument();
libxml_use_internal_errors(true);
$dom->loadHTML($html);
libxml_clear_errors();

$xpath = new DOMXPath($dom);
// Find the first div with class 'info'.
$infodivs = $xpath->query('//div[contains(@class, "info")]');
if ($infodivs->length > 0) {
    $infodiv = $infodivs->item(0);
    $parentdiv = $infodiv->parentNode;
    if ($parentdiv->nodeName === 'div') {
        // Collect parent div attributes into an array.
        $attrs = [];
        foreach ($parentdiv->attributes as $attr) {
            $attrs[$attr->nodeName] = $attr->nodeValue;
        }
        $infohtml = $dom->saveHTML($infodiv);
        echo html_writer::start_tag('div', $attrs);
        echo $infohtml;
        echo html_writer::end_tag('div');
    }
}
$mform->display();
echo $OUTPUT->footer();
