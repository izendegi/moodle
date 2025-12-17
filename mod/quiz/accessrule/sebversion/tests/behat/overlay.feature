@quizaccess @quizaccess_sebversion @javascript
Feature: Test the modal overlay
    As a teacher
    In order to enforce a certain SEB version with the quizaccess_sebversion plugin
    I must be sure that the modal overlay will be shown if needed

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

  Scenario: Test with simulated non-standard SEB
    Given I simulate Safe Exam Browser version "foobar" for the sebversion quizaccess plugin
    When I press "Attempt quiz"
    Then I should see "The version of your Safe Exam Browser could not be determined. Please download the most recent official version and try again."
    And the focused element is "" "quizaccess_sebversion > modal overlay"
    And I should not be able to click on "iframe[class^='tox-edit-area']" because of the sebversion quizaccess overlay

  Scenario: Test simulating there is no SEB
    Given I simulate Safe Exam Browser version "no SEB" for the sebversion quizaccess plugin
    When I press "Attempt quiz"
    Then I should see "The version of your Safe Exam Browser could not be determined. Please download the most recent official version and try again."
    And the focused element is "" "quizaccess_sebversion > modal overlay"
    And I should not be able to click on "iframe[class^='tox-edit-area']" because of the sebversion quizaccess overlay

  Scenario: Test with simulated older SEB on Mac
    Given I simulate Safe Exam Browser version "Safe Exam Browser_macOS_3.5_15487_org.safeexambrowser.SafeExamBrowser" for the sebversion quizaccess plugin
    When I press "Attempt quiz"
    Then I should see "Please update your Safe Exam Browser in order to attempt this quiz. You need at least version 3.6.0."
    And the focused element is "" "quizaccess_sebversion > modal overlay"
    And I should not be able to click on "iframe[class^='tox-edit-area']" because of the sebversion quizaccess overlay

  Scenario: Test with simulated older SEB on iOS
    Given I simulate Safe Exam Browser version "Safe Exam Browser_iOS_3.5_15487_org.safeexambrowser.SafeExamBrowser" for the sebversion quizaccess plugin
    When I press "Attempt quiz"
    Then I should see "Please update your Safe Exam Browser in order to attempt this quiz. You need at least version 3.6.0."
    And the focused element is "" "quizaccess_sebversion > modal overlay"
    And I should not be able to click on "iframe[class^='tox-edit-area']" because of the sebversion quizaccess overlay

  Scenario: Test with simulated matching SEB on Mac
    Given I simulate Safe Exam Browser version "Safe Exam Browser_macOS_3.6_156D0_org.safeexambrowser.SafeExamBrowser" for the sebversion quizaccess plugin
    When I press "Attempt quiz"
    Then I should not see "Please update your Safe Exam Browser in order to attempt this quiz. You need at least version 3.6.0."
    And "" "quizaccess_sebversion > modal overlay" should not exist

  Scenario: Test with simulated matching SEB on iOS
    Given I simulate Safe Exam Browser version "Safe Exam Browser_iOS_3.6_156D0_org.safeexambrowser.SafeExamBrowser" for the sebversion quizaccess plugin
    When I press "Attempt quiz"
    Then I should not see "Please update your Safe Exam Browser in order to attempt this quiz. You need at least version 3.6.0."
    And "" "quizaccess_sebversion > modal overlay" should not exist

  Scenario: Test with simulated newer SEB (patchlevel) on Mac
    Given I simulate Safe Exam Browser version "Safe Exam Browser_macOS_3.6.1_15708_org.safeexambrowser.SafeExamBrowser" for the sebversion quizaccess plugin
    When I press "Attempt quiz"
    Then I should not see "Please update your Safe Exam Browser in order to attempt this quiz. You need at least version 3.6.0."
    And "" "quizaccess_sebversion > modal overlay" should not exist

  Scenario: Test with simulated newer SEB (minor) on Mac
    Given I simulate Safe Exam Browser version "Safe Exam Browser_macOS_3.8_xxxxx_org.safeexambrowser.SafeExamBrowser" for the sebversion quizaccess plugin
    When I press "Attempt quiz"
    Then I should not see "Please update your Safe Exam Browser in order to attempt this quiz. You need at least version 3.6.0."
    And "" "quizaccess_sebversion > modal overlay" should not exist

  Scenario: Test with simulated newer SEB (major) on Mac
    Given I simulate Safe Exam Browser version "Safe Exam Browser_macOS_4.1_xxxxx_org.safeexambrowser.SafeExamBrowser" for the sebversion quizaccess plugin
    When I press "Attempt quiz"
    Then I should not see "Please update your Safe Exam Browser in order to attempt this quiz. You need at least version 3.6.0."
    And "" "quizaccess_sebversion > modal overlay" should not exist

  Scenario: Test with simulated older SEB on Windows
    Given I simulate Safe Exam Browser version "SEB_Windows_3.9.0.787" for the sebversion quizaccess plugin
    When I press "Attempt quiz"
    Then I should see "Please update your Safe Exam Browser in order to attempt this quiz. You need at least version 3.10.0."
    And the focused element is "" "quizaccess_sebversion > modal overlay"
    And I should not be able to click on "iframe[class^='tox-edit-area']" because of the sebversion quizaccess overlay

  Scenario: Test with simulated matching SEB on Windows
    Given I simulate Safe Exam Browser version "SEB_Windows_3.10.0.826" for the sebversion quizaccess plugin
    When I press "Attempt quiz"
    Then I should not see "Please update your Safe Exam Browser in order to attempt this quiz. You need at least version 3.10.0."
    And "" "quizaccess_sebversion > modal overlay" should not exist

  Scenario: Test with simulated newer SEB (patchlevel) on Windows
    Given I simulate Safe Exam Browser version "SEB_Windows_3.10.1.xxx" for the sebversion quizaccess plugin
    When I press "Attempt quiz"
    Then I should not see "Please update your Safe Exam Browser in order to attempt this quiz. You need at least version 3.10.0."
    And "" "quizaccess_sebversion > modal overlay" should not exist

  Scenario: Test with simulated newer SEB (minor) on Windows
    Given I simulate Safe Exam Browser version "SEB_Windows_3.11.0.xxx" for the sebversion quizaccess plugin
    When I press "Attempt quiz"
    Then I should not see "Please update your Safe Exam Browser in order to attempt this quiz. You need at least version 3.10.0."
    And "" "quizaccess_sebversion > modal overlay" should not exist

  Scenario: Test with simulated newer SEB (major) on Windows
    Given I simulate Safe Exam Browser version "SEB_Windows_4.8.0.xxx" for the sebversion quizaccess plugin
    When I press "Attempt quiz"
    Then I should not see "Please update your Safe Exam Browser in order to attempt this quiz. You need at least version 3.10.0."
    And "" "quizaccess_sebversion > modal overlay" should not exist
