@mod @mod_quiz @quiz @quiz_answersheets
Feature: Edit attachment feature of the Answer sheets report
  In order to allow staff to edit essay attachments after an attempt is finished
  As a teacher with submitresponses capability
  I need to see and use the "Staff only: Edit attachment" link in the Review screen

  Background:
    Given the following "users" exist:
      | username | firstname | lastname |
      | teacher  | The       | Teacher  |
      | teacher2 | Limited   | Teacher  |
      | student1 | Student   | One      |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher  | C1     | editingteacher |
      | teacher2 | C1     | teacher        |
      | student1 | C1     | student        |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype | name      | questiontext    | template         |
      # essay-001 uses the "editorfilepicker" template which enables file attachments (attachments > 0), required for the edit link to appear.
      | Test questions   | essay | essay-001 | Second question | editorfilepicker |
      # essay-002 has no template, so attachments are disabled (attachments = 0), which blocks the edit link from appearing.
      | Test questions   | essay | essay-002 | Third question  |                  |
    And the following "activities" exist:
      | activity | name   | intro              | course | idnumber | timeclose  |
      # timeclose 1774744309 is a Unix timestamp (year 2026) that is already in the past, satisfying the condition timeclose < now() for the edit link to appear.
      | quiz     | Quiz 1 | Quiz 1 description | C1     | quiz1    | 1774744309 |
    And quiz "Quiz 1" contains the following questions:
      | question  | page |
      | essay-001 | 1    |
    And user "student1" has attempted "Quiz 1" with responses:
      | slot | response      |
      | 1    | Sample answer |

  @javascript
  Scenario: Edit attachment link is not shown for an in-progress attempt
    Given the following "activities" exist:
      | activity | name   | intro              | course | idnumber |
      | quiz     | Quiz 2 | Quiz 2 description | C1     | quiz2    |
    And quiz "Quiz 2" contains the following questions:
      | question  | page |
      | essay-001 | 1    |
    And user "student1" has started an attempt at quiz "Quiz 2"
    When I am on the "Quiz 2" "quiz_answersheets > Report" page logged in as "teacher"
    Then I should not see "Review sheet"
    And I should not see "Staff only: Edit attachment"

  @javascript
  Scenario Outline: Edit attachment link is not shown when finished attempts do not meet attachment edit conditions
    Given the following "activities" exist:
      | activity | name       | intro                  | course | idnumber       | timeclose   |
      | quiz     | <quizname> | <quizname> description | C1     | <quizidnumber> | <timeclose> |
    And quiz "<quizname>" contains the following questions:
      | question   | page |
      | <question> | 1    |
    And user "student1" has attempted "<quizname>" with responses:
      | slot | response      |
      | 1    | Sample answer |
    When I am on the "<quizname>" "quiz_answersheets > Report" page logged in as "teacher"
    And I follow "Review sheet"
    Then I should not see "Staff only: Edit attachment"

    Examples:
      | quizname | quizidnumber | timeclose  | question  |
      # Quiz 3: timeclose 9999999999 is a far-future timestamp (year 2286), so the quiz has not yet closed — edit link must not appear.
      | Quiz 3   | quiz3        | 9999999999 | essay-001 |
      # Quiz 4: timeclose 0 means no close date is set in Moodle — edit link must not appear.
      | Quiz 4   | quiz4        | 0          | essay-001 |
      # Quiz 5: uses essay-002 which has no attachment template (attachments = 0) — edit link must not appear even though quiz is closed.
      | Quiz 5   | quiz5        | 1774744309 | essay-002 |

  @javascript
  Scenario: Staff without submitresponses capability cannot see Edit attachment link
    # Prevent the submitresponses capability on the "teacher" role at course level.
    # teacher2 has the "teacher" role in C1, so this override removes the capability for teacher2.
    Given the following "permission overrides" exist:
      | capability                        | permission | role    | contextlevel | reference |
      | quiz/answersheets:submitresponses | Prevent    | teacher | Course       | C1        |
    When I am on the "Quiz 1" "quiz_answersheets > Report" page logged in as "teacher2"
    And I follow "Review sheet"
    Then I should not see "Staff only: Edit attachment"

  @javascript @_file_upload
  Scenario: Edited attachment history shows the editor to staff and hides it from students
    When I am on the "Quiz 1" "quiz_answersheets > Report" page logged in as "teacher"
    And I follow "Review sheet"
    Then I should see "Staff only: Edit attachment"
    And I follow "Staff only: Edit attachment"
    And I should see "Editing student response"
    And "Save response on behalf of Student One" "button" should exist
    # "Submit responses on behalf of" is the title on the submit-all page; the edit-attachment page must show "Editing student response" instead.
    And I should not see "Submit responses on behalf of"
    And I upload "question/type/essay/tests/fixtures/1.png" file to "Attachments" filemanager
    And I press "Save response on behalf of Student One"
    # Verify as teacher (the editor): the attachment and editor name are visible in the review screen.
    And I am on the "Quiz 1 > student1 > Attempt 1" "mod_quiz > Attempt review" page logged in as "teacher"
    And I should see "1.png"
    And I should see "The Teacher"
    And "The Teacher" "link" should exist
    # Verify as teacher2 (another staff member): the editor identity is still visible to all staff.
    And I am on the "Quiz 1 > student1 > Attempt 1" "mod_quiz > Attempt review" page logged in as "teacher2"
    And I should see "The Teacher"
    And "The Teacher" "link" should exist
    # Verify as student1: the attachment is visible but the editor's identity is hidden.
    And I am on the "Quiz 1 > student1 > Attempt 1" "mod_quiz > Attempt review" page logged in as "student1"
    And I should see "1.png"
    And "The Teacher" "link" should not exist
    # The system does not render a "Moderator" label; it simply hides the editor's real name from students.
    And I should not see "Moderator"
    And I should not see "Staff only: Edit attachment"
