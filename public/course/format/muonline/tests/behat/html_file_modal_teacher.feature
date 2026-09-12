@format @format_muonline @format_muonline_mod_modal @format_muonline_html_modal_teacher @javascript @_file_upload
Feature: HTML file can be set to open in modal windows with submuonline off
  In order to improve UX
  As a user
  I need to be able to use these modals

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | 1        | student1@example.com |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format | coursedisplay | numsections | enablecompletion |
      | Course 1 | C1        | muonline  | 0             | 5           | 1                |
    And the following "activities" exist:
      | activity | name           | intro                 | course | idnumber | section | visible |
      | page     | Test page name | Test page description | C1     | page1    | 1       | 1       |
    And the following "activities" exist:
      | activity | name           | intro                 | course | idnumber | section | visible | completion | defaultfilename                              | uploaded |
      | page     | Test page name | Test page description | C1     | page1    | 1       | 1       | 0          |                                              | 0        |
      | resource | Test HTML file | File description      | C1     | pdf1     | 1       | 1       | 1          | course/format/muonline/tests/fixtures/test.html | 1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | C1     | student        |
      | teacher1 | C1     | editingteacher |
    And the following config values are set as admin:
      | config                 | value    | plugin       |
      | enablecompletion       | 1        | core         |
      | modalmodules           | page     | format_muonline |
      | modalresources         | pdf,html | format_muonline |
      | assumedatastoreconsent | 1        | format_muonline |
      | reopenlastsection      | 0        | format_muonline |
      | usejavascriptnav       | 1        | format_muonline |

    When I log in as "teacher1"
    And format_muonline submuonline are off for course "Course 1"
    And I am on "Course 1" course homepage with editing mode on
    Then I should see "Test HTML file"
    And I log out muonline

  #  First check can see the HTML with submuonline off
  @javascript
  Scenario: Open section 1 view HTML file as teacher with submuonline off
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And format_muonline submuonline are off for course "Course 1"
    And I click on tile "1"
    And I wait until the page is ready
    And I click format muonline activity "Test HTML file"
    And I wait until the page is ready
    Then "Test HTML file" "dialogue" should be visible
#    TODO test that we can see "Test HTML file content" too (is in embedded HTML document virtual element)?

    And format_muonline progress for "resource" called "Test HTML file" in "Course 1" is "0" in the database
    And I click on "Mark as done" "button" in the "Test HTML file" "dialogue"
    And I wait until the page is ready
    And format_muonline progress for "resource" called "Test HTML file" in "Course 1" is "1" in the database
    And I click on "Done" "button" in the "Test HTML file" "dialogue"
    And I wait until the page is ready
    And format_muonline progress for "resource" called "Test HTML file" in "Course 1" is "0" in the database

    And "Close" "button" should exist in the "Test HTML file" "dialogue"
    And I click on "Close" "button" in the "Test HTML file" "dialogue"
    And I wait until the page is ready
    And "Test HTML file" "dialogue" should not be visible
    And I click on close button for tile "1"
    And I log out muonline

#  Now with submuonline on
  @javascript
  Scenario: Open section 1 add HTML file as teacher with submuonline on
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And format_muonline submuonline are on for course "Course 1"
    And I click on tile "1"
    And I wait until the page is ready
    And I click format muonline activity "Test HTML file"
    And I wait until the page is ready
    Then "Test HTML file" "dialogue" should be visible
    #    TODO test that we can see "Test HTML file content" too (is in embedded HTML document virtual element)?
    And format_muonline progress for "resource" called "Test HTML file" in "Course 1" is "0" in the database
    And I click on "Mark as done" "button" in the "Test HTML file" "dialogue"
    And I wait until the page is ready
    And format_muonline progress for "resource" called "Test HTML file" in "Course 1" is "1" in the database
    And I click on "Done" "button" in the "Test HTML file" "dialogue"
    And I wait until the page is ready
    And format_muonline progress for "resource" called "Test HTML file" in "Course 1" is "0" in the database
    And "Close" "button" should exist in the "Test HTML file" "dialogue"
    And I click on "Close" "button" in the "Test HTML file" "dialogue"
    And I wait until the page is ready
    And "Test HTML file" "dialogue" should not be visible
    And I click on close button for tile "1"
    And I log out muonline
