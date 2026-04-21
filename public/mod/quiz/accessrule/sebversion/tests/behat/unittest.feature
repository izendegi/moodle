@quizaccess @quizaccess_sebversion @javascript
Feature: Test version comparison
    As a teacher
    In order to enforce a certain SEB version with the quizaccess_sebversion plugin
    I must be sure that the comparison of versions works as intended

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
    And I log in as "student"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"

  Scenario: Test with valid SEB, looking only for the output of the unit test function
    Given I simulate Safe Exam Browser version "Safe Exam Browser_macOS_3.6_156D0_org.safeexambrowser.SafeExamBrowser" for the sebversion quizaccess plugin
    When I press "Attempt quiz"
    Then I should not see "Please update your Safe Exam Browser in order to attempt this quiz. You need at least version 3.6.0."
    And "" "quizaccess_sebversion > modal overlay" should not exist
    And I should see "quizaccess_sebversion unit tests successful"
    And I should not see "quizaccess_sebversion unit test failed"
