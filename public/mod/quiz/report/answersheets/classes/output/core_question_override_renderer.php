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

namespace quiz_answersheets\output;

use mod_quiz\quiz_attempt;
use quiz_answersheets\utils;
use question_attempt;
use qbehaviour_renderer;
use qtype_renderer;
use question_display_options;
use html_writer;

/**
 * The override core_question_renderer for the quiz_answersheets module.
 *
 * @copyright  2019 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_question_override_renderer extends \core_question_renderer {
    #[\Override]
    protected function formulation(
        question_attempt $qa,
        qbehaviour_renderer $behaviouroutput,
        qtype_renderer $qtoutput,
        question_display_options $options
    ): string {
        global $attemptobj;
        // We need to use global trick here because in mod/quiz/report/answersheets/submitresponses.php:23
        // already loaded the attemptobj so we no need to do the extra Database query.
        $output = '';

        $rightanswer = $this->page->url->get_param('rightanswer');

        if (
            $rightanswer
            && $attemptobj->get_state() == quiz_attempt::IN_PROGRESS
            || $this->page->pagetype == 'mod-quiz-report-answersheets-submitresponses'
        ) {
            // Do not show question instruction for right answer sheet and submit responses page.
            return parent::formulation($qa, $behaviouroutput, $qtoutput, $options);
        }

        // Show default instruction if ticked.
        if ((bool) $this->page->url->get_param('instruction')) {
            // Append question instruction if exist.
            $output .= $this->render_question_instruction($qa);
        }
        $output .= parent::formulation($qa, $behaviouroutput, $qtoutput, $options);

        return $output;
    }

    #[\Override]
    protected function status(
        question_attempt $qa,
        qbehaviour_renderer $behaviouroutput,
        question_display_options $options
    ): string {
        // Do not show the question status.
        return '';
    }

    /**
     * Render question instruction
     *
     * @param question_attempt $qa Question attempt
     * @return string HTML string
     */
    private function render_question_instruction(question_attempt $qa) {
        $output = '';
        $question = $qa->get_question();

        if ($question->get_type_name() == 'combined') {
            // Specific code for Combined question type.
            // Get all sub questions. We need to user reflection method because it is a protected property.
            $subqs = utils::get_reflection_property($question->combiner, 'subqs');
            $output .= html_writer::start_div('question-instruction');
            if (count($subqs) > 1) {
                $output .= html_writer::start_tag('ul', ['class' => 'list']);
                $subqslist = [];
                foreach ($subqs as $subq) {
                    // Get sub question type name.
                    $qtypename = utils::get_reflection_property($subq->type, 'qtypename');
                    if (!in_array($qtypename, $subqslist)) {
                        $subqslist[] = $qtypename;
                    } else {
                        continue;
                    }
                    $qinstruction = utils::get_question_instruction($qtypename);
                    if (!empty($qinstruction)) {
                        $output .= html_writer::tag('li', $qinstruction);
                    }
                }
                $output .= html_writer::end_tag('ul');
            } else {
                $subq = $subqs[0];
                $qtypename = utils::get_reflection_property($subq->type, 'qtypename');
                $qinstruction = utils::get_question_instruction($qtypename);
                if (!empty($qinstruction)) {
                    $output .= $qinstruction;
                }
            }
            $output .= html_writer::end_div();
        } else {
            // Normal question type.
            $qinstruction = utils::get_question_instruction($question->get_type_name());
            if (!empty($qinstruction)) {
                $output .= html_writer::div($qinstruction, 'question-instruction');
            }
        }

        return $output;
    }

    #[\Override]
    public function question(
        question_attempt $qa,
        qbehaviour_renderer $behaviouroutput,
        qtype_renderer $qtoutput,
        question_display_options $options,
        $number
    ): string {
        $rightanswer = $this->page->url->get_param('rightanswer');
        $showinlinefeedback = (bool) $this->page->url->get_param('showinlinefeedback');
        $showcombinefeedback = (bool) $this->page->url->get_param('showcombinefeedback');
        $showgeneralfeedback = (bool) $this->page->url->get_param('showgeneralfeedback');

        $output = '';
        if (!$showinlinefeedback && isset($options->feedback)) {
            $options->feedback = question_display_options::HIDDEN;
        }
        if (!$showgeneralfeedback && isset($options->generalfeedback)) {
            $options->generalfeedback = question_display_options::HIDDEN;
        }

        $output .= parent::question($qa, $behaviouroutput, $qtoutput, $options, $number);
        $feedback = '';
        if (
            utils::should_show_combined_feedback($qa->get_question()->get_type_name())
            && $rightanswer
            && $showcombinefeedback
        ) {
            $feedback .= $this->render_question_combined_feedback($qa);
            if ($showgeneralfeedback) {
                $feedback .= $this->render_question_general_feedback($qa);
            }
        }

        if (!empty($feedback)) {
            $feedback = \html_writer::div($feedback, 'question-feedback');
        }

        $output .= $feedback;

        return $output;
    }

    /**
     * Render question general feedback.
     *
     * @param question_attempt $qa Question attempt.
     * @return string HTML string.
     */
    public function render_question_general_feedback(question_attempt $qa): string {
        $generalfeedback = $qa->get_question()->format_generalfeedback($qa);
        $feedback = '';
        if (!empty($generalfeedback)) {
            $feedback .= \html_writer::tag(
                'h3',
                get_string('combine_feedback_general', 'quiz_answersheets'),
                ['class' => 'question-feedback-title']
            );
            $feedback .= \html_writer::div($generalfeedback, 'question-feedback-content');
        }

        return $feedback;
    }

    /**
     * Render question combined feedback
     *
     * @param question_attempt $qa Question attempt
     * @return string HTML string
     */
    public function render_question_combined_feedback(question_attempt $qa) {
        $feedback = '';
        $incorrectfeedback = $this->get_combine_feedback($qa, 'incorrect');
        $partialfeedback = $this->get_combine_feedback($qa, 'partiallycorrect');
        $correctfeedback = $this->get_combine_feedback($qa, 'correct');

        if (!empty($incorrectfeedback)) {
            $feedback .= \html_writer::tag(
                'h3',
                get_string('combine_feedback_incorrect', 'quiz_answersheets'),
                ['class' => 'question-feedback-title']
            );
            $feedback .= \html_writer::div($incorrectfeedback, 'question-feedback-content');
        }
        if (!empty($partialfeedback)) {
            $feedback .= \html_writer::tag(
                'h3',
                get_string('combine_feedback_partially_correct', 'quiz_answersheets'),
                ['class' => 'question-feedback-title']
            );
            $feedback .= \html_writer::div($partialfeedback, 'question-feedback-content');
        }
        if (!empty($correctfeedback)) {
            $feedback .= \html_writer::tag(
                'h3',
                get_string('combine_feedback_correct', 'quiz_answersheets'),
                ['class' => 'question-feedback-title']
            );
            $feedback .= \html_writer::div($correctfeedback, 'question-feedback-content');
        }

        return $feedback;
    }

    /**
     * Get the combine feedback for given question.
     *
     * @param question_attempt $qa Question attempt
     * @param string $type Type of feedback
     * @return string Combine feedback
     */
    public function get_combine_feedback(question_attempt $qa, string $type) {
        $question = $qa->get_question();
        $feedback = '';
        $field = $type . 'feedback';
        $format = $type . 'feedbackformat';
        if (isset($question->$field) && $question->$field) {
            $feedback .= $question->format_text($question->$field, $question->$format, $qa, 'question', $field, $question->id);
            if ($type == 'partiallycorrect' && $question->get_type_name() == 'oumultiresponse') {
                $feedback .= \html_writer::div(get_string('toomanyselected', 'qtype_multichoice'));
            }
        }

        return $feedback;
    }
}
