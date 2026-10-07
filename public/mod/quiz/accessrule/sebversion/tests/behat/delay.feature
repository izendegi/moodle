@quizaccess @quizaccess_sebversion @javascript
Feature: Test dealing with delayed availability of the SEB object
    As a teacher
    If the SEB's Javascript API is made available only after a short delay
    I must be sure that quizaccess_sebversion plugin will retry

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

  Scenario: Test a valid version with a short delay
    Given I wait "9" seconds before simulating Safe Exam Browser version "SEB_Windows_3.10.0.826" for the sebversion quizaccess plugin
    When I press "Attempt quiz"
    Then "" "quizaccess_sebversion > modal overlay" should not exist
    When I wait "15" seconds
    Then I should not see "Please update your Safe Exam Browser in order to attempt this quiz. You need at least version 3.10.0."
    And "" "quizaccess_sebversion > modal overlay" should not exist

  Scenario: Test an outdated version with a short delay
    Given I wait "9" seconds before simulating Safe Exam Browser version "SEB_Windows_3.9.0.787" for the sebversion quizaccess plugin
    When I press "Attempt quiz"
    Then "" "quizaccess_sebversion > modal overlay" should not exist
    When I wait "12" seconds
    Then I should see "Please update your Safe Exam Browser in order to attempt this quiz. You need at least version 3.10.0."
    And the focused element is "" "quizaccess_sebversion > modal overlay"
    And I should not be able to click on "iframe[class^='tox-edit-area']" because of the sebversion quizaccess overlay

  Scenario: Test a valid version with a long delay
    Given I wait "15" seconds before simulating Safe Exam Browser version "SEB_Windows_3.10.0.826" for the sebversion quizaccess plugin
    When I press "Attempt quiz"
    Then "" "quizaccess_sebversion > modal overlay" should not exist
    When I wait "12" seconds
    Then I should see "The version of your Safe Exam Browser could not be determined. Wait a few seconds and try to reload the page. If this does not solve the problem, please install the most recent official version and try again."
    And the focused element is "" "quizaccess_sebversion > modal overlay"
    And I should not be able to click on "iframe[class^='tox-edit-area']" because of the sebversion quizaccess overlay

  Scenario: Test an outdated version with a long delay
    Given I wait "15" seconds before simulating Safe Exam Browser version "SEB_Windows_3.9.0.787" for the sebversion quizaccess plugin
    When I press "Attempt quiz"
    Then "" "quizaccess_sebversion > modal overlay" should not exist
    When I wait "12" seconds
    Then I should see "The version of your Safe Exam Browser could not be determined. Wait a few seconds and try to reload the page. If this does not solve the problem, please install the most recent official version and try again."
    And the focused element is "" "quizaccess_sebversion > modal overlay"
    And I should not be able to click on "iframe[class^='tox-edit-area']" because of the sebversion quizaccess overlay

  Scenario: Test a simulated non-standard SEB with a long delay
    Given I wait "15" seconds before simulating Safe Exam Browser version "foobar" for the sebversion quizaccess plugin
    When I press "Attempt quiz"
    Then "" "quizaccess_sebversion > modal overlay" should not exist
    When I wait "12" seconds
    Then I should see "The version of your Safe Exam Browser could not be determined. Wait a few seconds and try to reload the page. If this does not solve the problem, please install the most recent official version and try again."
    And the focused element is "" "quizaccess_sebversion > modal overlay"
    And I should not be able to click on "iframe[class^='tox-edit-area']" because of the sebversion quizaccess overlay
