@quizaccess @quizaccess_sebversion @javascript
Feature: Test editing a quiz
    As a teacher
    In order to use the quizaccess_sebversion plugin
    I must be able to enable and disable enforcing the SEB version

  Background:
    Given the following "users" exist:
      | username |
      | teacher  |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | C1     | editingteacher |
    And the following "activity" exists:
      | activity                   | quiz     |
      | course                     | C1       |
      | idnumber                   | quizseb  |
      | name                       | Quiz SEB |
      | seb_requiresafeexambrowser | 1        |
    And the following "activity" exists:
      | activity                   | quiz        |
      | course                     | C1          |
      | idnumber                   | quiznoseb   |
      | name                       | Quiz No SEB |
      | seb_requiresafeexambrowser | 0           |
    And the following "activity" exists:
      | activity                   | quiz           |
      | course                     | C1             |
      | idnumber                   | quiznoseb      |
      | name                       | Quiz Enforcing |
      | seb_requiresafeexambrowser | 1              |
      | sebversion_enforce         | 1              |
    And I log in as "teacher"

  Scenario: The option field is hidden and shown depending on the SEB requirement
    When I am on the "Quiz No SEB" "quiz activity editing" page
    And I follow "Safe Exam Browser"
    Then I should not see "Enforce minimum SEB version"
    When I set the field "Require the use of Safe Exam Browser" to "Yes – Configure manually"
    Then I should see "Enforce minimum SEB version"

  Scenario: Setting fetched from the DB and set in form when editing a quiz
    When I am on the "Quiz Enforcing" "quiz activity editing" page
    And I follow "Safe Exam Browser"
    Then I should see "Enforce minimum SEB version"
    And the field "Enforce minimum SEB version" matches value "1"

  Scenario: Setting is saved to the DB when preparing when editing a quiz
    When I am on the "Quiz SEB" "quiz activity editing" page
    And I follow "Safe Exam Browser"
    And I set the field "Enforce minimum SEB version" to "Yes"
    And I press "Save and display"
    And I am on the "Quiz SEB" "quiz activity editing" page
    And I follow "Safe Exam Browser"
    Then I should see "Enforce minimum SEB version"
    And the field "Enforce minimum SEB version" matches value "1"
    When I set the field "Enforce minimum SEB version" to "No"
    And I press "Save and display"
    And I am on the "Quiz SEB" "quiz activity editing" page
    And I follow "Safe Exam Browser"
    Then I should see "Enforce minimum SEB version"
    And the field "Enforce minimum SEB version" matches value "0"

  Scenario: Configured default value (ON) is used when creating a new quiz
    Given the following config values are set as admin:
      | enforcedefault | 1 | quizaccess_sebversion |
    When I add a quiz activity to course "Course 1" section "0" and I fill the form with:
      | Name                       | New Quiz |
      | seb_requiresafeexambrowser | 1        |
    And I am on the "New Quiz" "quiz activity editing" page
    And I follow "Safe Exam Browser"
    Then I should see "Enforce minimum SEB version"
    And the field "Enforce minimum SEB version" matches value "1"

  Scenario: Configured default value (OFF) is used when creating a new quiz
    Given the following config values are set as admin:
      | enforcedefault | 0 | quizaccess_sebversion |
    When I add a quiz activity to course "Course 1" section "0" and I fill the form with:
      | Name                       | Other New Quiz |
      | seb_requiresafeexambrowser | 1              |
    And I am on the "Other New Quiz" "quiz activity editing" page
    And I follow "Safe Exam Browser"
    Then I should see "Enforce minimum SEB version"
    And the field "Enforce minimum SEB version" matches value "0"
