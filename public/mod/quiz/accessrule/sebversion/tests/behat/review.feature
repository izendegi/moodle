@quizaccess @quizaccess_sebversion @javascript
Feature: Test there is no overlay during review
    As a student
    In order to review my answers in a normal browser
    the plugin must not block me with a modal overlay during review

  Background:
    Given the following config values are set as admin:
      | minversionmac | 3.6.0  | quizaccess_sebversion |
      | minversionwin | 3.10.0 | quizaccess_sebversion |
    And the following "users" exist:
      | username |
      | student  |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user    | course | role    |
      | student | C1     | student |
    And the following "activity" exists:
      | activity           | quiz   |
      | course             | C1     |
      | idnumber           | 00001  |
      | name               | Quiz 1 |
      | sebversion_enforce | 1      |
    And the following "question categories" exist:
      | contextlevel | reference | name |
      | Course       | C1        | Cat1 |
    And the following "questions" exist:
      | questioncategory | qtype | name | questiontext                  |
      | Cat1             | essay | Q1   | Write about whatever you want |
    And quiz "Quiz 1" contains the following questions:
      | question | page |
      | Q1       | 1    |
    And user "student" has attempted "Quiz 1" with responses:
      | slot | response           |
      | 1    | This is my answer. |
    And I log in as "student"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"

  Scenario: Test review
    Given I follow "Review"
    Then I should not see "The veresion of your Safe Exam Browser could not be determined."
    And "" "quizaccess_sebversion > modal overlay" should not exist
    When I follow "Finish review"
    Then I should see "Your attempts"
