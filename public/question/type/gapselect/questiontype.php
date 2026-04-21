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
 * Question type class for the select missing words question type. - wordtable customisation.
 *
 * @package    qtype_gapselect
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/format/xml/format.php');
require_once($CFG->dirroot . '/question/type/gapselect/questiontypebase.php');


/**
 * The select missing words question type class.
 *
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_gapselect extends qtype_gapselect_base {
    protected function choice_options_to_feedback($choice) {
        return $choice['choicegroup'];
    }

    protected function make_choice($choicedata) {
        return new qtype_gapselect_choice($choicedata->answer, $choicedata->feedback);
    }

    protected function feedback_to_choice_options($feedback) {
        return array('selectgroup' => $feedback);
    }


    protected function choice_group_key() {
        return 'selectgroup';
    }

    public function import_from_xml($data, $question, qformat_xml $format, $extra=null) {
        if (!isset($data['@']['type']) || $data['@']['type'] != 'gapselect') {
            return false;
        }

        $question = $format->import_headers($data);
        $question->qtype = 'gapselect';

        $question->shuffleanswers = $format->trans_single(
                $format->getpath($data, array('#', 'shuffleanswers', 0, '#'), 1));

        if (!empty($data['#']['selectoption'])) {
            // Modern XML format.
            $selectoptions = $data['#']['selectoption'];
            $question->answer = array();
            $question->selectgroup = array();

            foreach ($data['#']['selectoption'] as $selectoptionxml) {
                $question->choices[] = array(
                    'answer'      => $format->getpath($selectoptionxml,
                                                      array('#', 'text', 0, '#'), '', true),
                    'choicegroup' => $format->getpath($selectoptionxml,
                                                      array('#', 'group', 0, '#'), 1),
                );
            }

        } else {
            // Legacy format containing PHP serialisation.
            foreach ($data['#']['answer'] as $answerxml) {
                $ans = $format->import_answer($answerxml);
                $question->choices[] = array(
                    'answer' => $ans->answer,
                    'choicegroup' => $ans->feedback,
                );
            }
        }

        $format->import_combined_feedback($question, $data, true);
        $format->import_hints($question, $data, true, false,
                $format->get_format($question->questiontextformat));

        return $question;
    }

    public function export_to_xml($question, qformat_xml $format, $extra = null) {
        $output = '';

        $output .= '    <shuffleanswers>' . $question->options->shuffleanswers .
                "</shuffleanswers>\n";

        $output .= $format->write_combined_feedback($question->options,
                                                    $question->id,
                                                    $question->contextid);

        foreach ($question->options->answers as $answer) {
            $output .= "    <selectoption>\n";
            $output .= $format->writetext($answer->answer, 3);
            $output .= "      <group>{$answer->feedback}</group>\n";
            $output .= "    </selectoption>\n";
        }

        return $output;
    }

    /**
     * Support import/export for wordtable format and export for htmltable format
     * cf. https://moodle.org/plugins/pluginversions.php?plugin=qformat_wordtable
     * cf. https://moodle.org/plugins/pluginversions.php?plugin=qformat_htmltable
     * Just call the corresponding XML functions
     */
    /**
     * Export to WordTable format
     *
     * A string containing XML is returned
     *
     * @param object $question the question definition data
     * @param object $format the qformat_xml object to be filled in
     * @param mixed $extra any additional format specific data that may be passed by the format
     * @return string the data to export
     */
    public function export_to_wordtable($question, qformat_xml $format, $extra=null) {
        return $this->export_to_xml($question, $format, $extra);
    }
    /**
     * Import from WordTable format
     *
     * A string containing XML is returned
     *
     * @param string $data questions inside an XML string, after being converted from Word
     * @param object $question the question definition data
     * @param object $format the qformat_xml object to be filled in
     * @param mixed $extra any additional format specific data that may be passed by the format
     * @return object the question definition data
     */
    public function import_from_wordtable($data, $question, qformat_xml $format, $extra=null) {
        return $this->import_from_xml($data, $question, $format, $extra);
    }

    /**
     * Export to HTML format
     *
     * A string containing XML is returned
     *
     * @param object $question the question definition data
     * @param object $format the qformat_xml object to be filled in
     * @param mixed $extra any additional format specific data that may be passed by the format
     * @return string the data to export
     */
    public function export_to_htmltable($question, qformat_xml $format, $extra=null) {
        return $this->export_to_xml($question, $format, $extra);
    }
}

