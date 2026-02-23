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
 * Unit tests filter name condition class.
 *
 * @package    qbank_filtername
 * @author     Mateusz Grzeszczak <mateusz.grzeszczak@p.lodz.pl>
 * @author     Mateusz Walczak <mateusz.walczak@p.lodz.pl>
 * @copyright  2024 TUL E-Learning Center
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_filtername;

use context;
use stdClass;
use moodle_url;
use context_course;
use advanced_testcase;
use core_course_category;
use core\output\datafilter;
use core_question\local\bank\view;
use core_question\local\bank\question_edit_contexts;

/**
 * Tests for filter name condition
 *
 * @covers \qbank_filtername\filter_name_condition
 */
final class filter_name_condition_test extends advanced_testcase {

    /** @var stdClass course record. */
    protected stdClass $course;

    /** @var context course context. */
    protected context $context;

    /** @var core_course_category course category record. */
    protected core_course_category $category;

    /** @var stdClass question category. */
    protected stdClass $qcategory;

    /** @var array questions to search. */
    protected array $questions;

    /**
     * Set up testcase with course and questions.
     */
    public function setUp(): void {
        parent::setUp();

        $this->resetAfterTest();
        $this->setAdminUser();

        $datagenarator = $this->getDataGenerator();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');

        $this->category = $datagenarator->create_category();
        $this->course = $datagenarator->create_course(['category' => $this->category->id]);
        $this->context = context_course::instance($this->course->id);

        $this->qcategory = $questiongenerator->create_question_category(['contextid' => $this->context->id]);

        $shortanswerquestion = $questiongenerator->create_question('shortanswer', overrides: [
            'category' => $this->qcategory->id,
            'name' => 'shortanswer',
            'questiontext' => ['text' => 'Short answer test question 1', 'format' => 1],
        ]);
        $multichoicequestion = $questiongenerator->create_question('multichoice', overrides: [
            'category' => $this->qcategory->id,
            'name' => 'MultiChoice',
            'questiontext' => ['text' => 'Multi choice test question 2', 'format' => 1],
        ]);
        $multianswerquestion = $questiongenerator->create_question('multianswer', overrides: [
            'category' => $this->qcategory->id,
            'name' => 'multianswer',
            'questiontext' => ['text' => 'Multi answer test question 3', 'format' => 1],
        ]);
        $essayquestion = $questiongenerator->create_question('essay', overrides: [
            'category' => $this->qcategory->id,
            'name' => 'Essay',
            'questiontext' => ['text' => 'Essay test question 4', 'format' => 1],
        ]);
        $this->questions = [
            'shortanswer' => $shortanswerquestion,
            'multichoice' => $multichoicequestion,
            'multianswer' => $multianswerquestion,
            'essay' => $essayquestion,
        ];
    }

    /**
     * Test filtering questions by name and question text.
     *
     * @dataProvider params_data_provider
     *
     * @param array $params filter params
     * @param array $questions array identifying which question should be found
     * @param int $resultsnumber number of results
     *
     * @return void
     */
    public function test_filter_question_by_name_and_text(array $params, array $questions, int $resultsnumber): void {
        $this->resetAfterTest();

        // We need to extend params with context and question category ids retrieved on setUp.
        // It's required because data provider is called before setUp function.
        $params['cat'] = $this->qcategory->id . ',' . $this->context->id;
        $params['filter']['category']['values'] = [$this->qcategory->id];

        $questionbank = new view(
                new question_edit_contexts($this->context),
                new moodle_url('/'),
                $this->course,
                params: $params
        );
        $questionbank->add_standard_search_conditions();
        $foundquestions = $questionbank->load_questions();

        $this->assertCount($resultsnumber, $foundquestions);
        foreach ($questions as $question => $shouldbefound) {
            if ($shouldbefound) {
                $this->assertArrayHasKey($this->questions[$question]->id, $foundquestions);
            } else {
                $this->assertArrayNotHasKey($this->questions[$question]->id, $foundquestions);
            }
        }
    }

    /**
     * Data provider for test_filter_question_by_name_and_text().
     *
     * @return array
     */
    public static function params_data_provider(): array {
        $params = [
            "qpage" => 0,
            "qperpage" => 1000,
            "cpage" => 1,
            "filter" => [
                "category" => [
                    "name" => "category",
                    "jointype" => datafilter::JOINTYPE_ANY,
                    "filteroptions" => [
                        [
                            "name" => "includesubcategories",
                            "value" => false,
                        ],
                    ],
                ],
                "hidden" => [
                    "name" => "hidden",
                    "jointype" => datafilter::JOINTYPE_ANY,
                    "values" => [0],
                    "filteroptions" => [],
                ],
                "filtername" => [
                    "name" => "filtername",
                    "jointype" => datafilter::JOINTYPE_ANY,
                    "values" => ["test"],
                    "filteroptions" => [
                        [
                            "name" => "filtertext",
                            "value" => true,
                        ],
                        [
                            "name" => "casesensitive",
                            "value" => false,
                        ],
                    ],
                ],
                "jointype" => datafilter::JOINTYPE_ALL,
            ],
            "tabname" => "questions",
            "sortdata" => [],
            "jointype" => datafilter::JOINTYPE_ALL,
        ];

        $params2 = $params;
        $params2['filter']['filtername']['values'] = ['short'];

        $params3 = $params;
        $params3['filter']['filtername']['values'] = ['short', 'multi'];

        $params4 = $params;
        $params4['filter']['filtername']['values'] = ['multi', 'choice'];
        $params4['filter']['filtername']['jointype'] = datafilter::JOINTYPE_ALL;

        $params5 = $params;
        $params5['filter']['filtername']['values'] = ['multi', 'answer'];
        $params5['filter']['filtername']['jointype'] = datafilter::JOINTYPE_NONE;

        $params6 = $params;
        $params6['filter']['filtername']['values'] = ['Test'];
        $params6['filter']['filtername']['filteroptions'][1]['value'] = true;

        $params7 = $params;
        $params7['filter']['filtername']['values'] = ['question'];
        $params7['filter']['filtername']['filteroptions'][0]['value'] = false;

        $params8 = $params;
        $params8['filter']['filtername']['values'] = ['Essay'];
        $params8['filter']['filtername']['filteroptions'][0]['value'] = false;
        $params8['filter']['filtername']['filteroptions'][1]['value'] = true;

        $params9 = $params;
        $params9['filter']['filtername']['values'] = ['Essay', 'multi'];
        $params9['filter']['filtername']['filteroptions'][0]['value'] = false;
        $params9['filter']['filtername']['filteroptions'][1]['value'] = true;

        return [
            'Case-insensitive filtering by name and text with "test" phrase.' => [
                'params' => $params,
                'questions' => [
                    'shortanswer' => true,
                    'multichoice' => true,
                    'multianswer' => true,
                    'essay' => true,
                ],
                'resultsnumber' => 4,
            ],
            'Case-insensitive filtering by name and text with "short" phrase.' => [
                'params' => $params2,
                'questions' => [
                    'shortanswer' => true,
                    'multichoice' => false,
                    'multianswer' => false,
                    'essay' => false,
                ],
                'resultsnumber' => 1,
            ],
            'Case-insensitive filtering by name and text with "short" or "multi" phrases.' => [
                'params' => $params3,
                'questions' => [
                    'shortanswer' => true,
                    'multichoice' => true,
                    'multianswer' => true,
                    'essay' => false,
                ],
                'resultsnumber' => 3,
            ],
            'Case-insensitive filtering by name and text with "multi" and "choice" phrases.' => [
                'params' => $params4,
                'questions' => [
                    'shortanswer' => false,
                    'multichoice' => true,
                    'multianswer' => false,
                    'essay' => false,
                ],
                'resultsnumber' => 1,
            ],
            'Case-insensitive filtering by name and text without "multi" and "answer" phrases.' => [
                'params' => $params5,
                'questions' => [
                    'shortanswer' => false,
                    'multichoice' => false,
                    'multianswer' => false,
                    'essay' => true,
                ],
                'resultsnumber' => 1,
            ],
            'Case-sensitive filtering by name and text with "Test" phrase.' => [
                'params' => $params6,
                'questions' => [
                    'shortanswer' => false,
                    'multichoice' => false,
                    'multianswer' => false,
                    'essay' => false,
                ],
                'resultsnumber' => 0,
            ],
            'Case-insensitive filtering by name with "question" phrase.' => [
                'params' => $params7,
                'questions' => [
                    'shortanswer' => false,
                    'multichoice' => false,
                    'multianswer' => false,
                    'essay' => false,
                ],
                'resultsnumber' => 0,
            ],
            'Case-sensitive filtering by name with "Essay" phrase.' => [
                'params' => $params8,
                'questions' => [
                    'shortanswer' => false,
                    'multichoice' => false,
                    'multianswer' => false,
                    'essay' => true,
                ],
                'resultsnumber' => 1,
            ],
            'Case-sensitive filtering by name with "Essay" or "multi" phrases.' => [
                'params' => $params9,
                'questions' => [
                    'shortanswer' => false,
                    'multichoice' => false,
                    'multianswer' => true,
                    'essay' => true,
                ],
                'resultsnumber' => 2,
            ],
        ];
    }
}
