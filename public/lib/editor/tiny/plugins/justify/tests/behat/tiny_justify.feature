@editor @editor_tiny @tiny @tiny_justify @javascript
Feature: Use the TinyMCE editor justify button
  In order to format text as justified
  As a teacher
  I need to be able to apply justify alignment using the TinyMCE editor

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                 |
      | teacher1 | Teacher   | 1        | teacher1@example.com  |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following "activity" exists:
      | activity | assign          |
      | course   | C1              |
      | name     | Test assignment |

  Scenario: The justify button appears in the TinyMCE toolbar
    Given I log in as "admin"
    And I open my profile in edit mode
    Then "Justify" button should exist in the "Description" TinyMCE editor

  Scenario: Applying justify alignment to selected text
    Given I log in as "admin"
    And I open my profile in edit mode
    And I set the field "Description" to "<p>Hello world</p>"
    And I select the "p" element in position "0" of the "Description" TinyMCE editor
    When I click on the "Justify" button for the "Description" TinyMCE editor
    Then the field "Description" matches value "<p style=\"text-align: justify;\">Hello world</p>"

  Scenario: Justify button toggles on and off
    Given I log in as "admin"
    And I open my profile in edit mode
    And I set the field "Description" to "<p>Toggle test</p>"
    And I select the "p" element in position "0" of the "Description" TinyMCE editor
    # Apply justify alignment.
    When I click on the "Justify" button for the "Description" TinyMCE editor
    Then the "Justify" button of the "Description" TinyMCE editor has state "true"
    # Remove justify alignment.
    When I click on the "Justify" button for the "Description" TinyMCE editor
    Then the "Justify" button of the "Description" TinyMCE editor has state "false"
    And the field "Description" matches value "<p>Toggle test</p>"

  Scenario: Justify is available via the Format menu
    Given I log in as "admin"
    And I open my profile in edit mode
    And I set the field "Description" to "<p>Menu test</p>"
    And I select the "p" element in position "0" of the "Description" TinyMCE editor
    When I click on the "Format > Align > Justify text" menu item for the "Description" TinyMCE editor
    Then the field "Description" matches value "<p style=\"text-align: justify;\">Menu test</p>"

  Scenario: Teacher can use justify in a course activity
    Given I log in as "teacher1"
    And I am on the "Test assignment" Activity page
    And I navigate to "Settings" in current page administration
    Then "Justify" button should exist in the "Activity instructions" TinyMCE editor
    And I set the field "Activity instructions" to "<p>Teacher content</p>"
    And I select the "p" element in position "0" of the "Activity instructions" TinyMCE editor
    When I click on the "Justify" button for the "Activity instructions" TinyMCE editor
    Then the field "Activity instructions" matches value "<p style=\"text-align: justify;\">Teacher content</p>"
