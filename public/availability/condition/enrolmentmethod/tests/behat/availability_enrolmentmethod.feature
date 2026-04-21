@availability @availability_enrolmentmethod
Feature: availability_enrolmentmethod
  In order to control student access to activities
  As a teacher
  I need to set enrolment method conditions which prevent student access

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format | enablecompletion | numsections |
      | Course 1 | C1        | topics | 1                | 3           |
    And the following "users" exist:
      | username |
      | teacher1 |
      | student1 |
      | student2 |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And I log in as "admin"
    And I am on "Course 1" course homepage
    And I navigate to "Users > Enrolment methods" in current page administration
    And I click on "Edit" "link" in the "Guest access" "table_row"
    And I set the following fields to these values:
      | Allow guest access | Yes |
    And I press "Save changes"
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I turn editing mode on
    And I add a "Page" to section "1"
    And I set the following fields to these values:
      | Name         | P1 |
      | Description  | x  |
      | Page content | x  |
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Enrolment method" "button" in the "Add restriction..." "dialogue"
    And I set the field "Enrolment method" to "Manual enrolments"
    And I click on ".availability-item .availability-eye img" "css_element"
    And I click on "Save and return to course" "button"
    And I log out

  @javascript
  Scenario: Try to view the page resource restricted to 'Manual enrolments" users with a user enrolled as 'Manual enrolments' and with a user enrolled as 'Guest access'.
    Given I log in as "student1"
    When I am on "Course 1" course homepage
    Then I should see "P1" in the "region-main" "region"
    And  I log out
    And I log in as "student2"
    When I am on "Course 1" course homepage
    Then I should not see "P1" in the "region-main" "region"
