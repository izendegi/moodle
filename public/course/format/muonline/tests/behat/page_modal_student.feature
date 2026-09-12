@format @format_muonline @format_muonline_mod_modal  @format_muonline_page_modal_student @javascript
Feature: Student can open a page

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | 1        | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format | coursedisplay | numsections | enablecompletion |
      | Course 5 | C5        | muonline  | 0             | 6           | 1                |
    And the following "activities" exist:
      | activity | name           | intro                 | course | idnumber | section | visible |
      | quiz     | Test quiz name | Test quiz description | C5     | quiz1    | 6       | 1       |
      | page     | Test page name | Test page description | C5     | page1    | 6       | 1       |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C5     | student |
    And the following config values are set as admin:
      | config                 | value    | plugin       |
      | enablecompletion       | 1        | core         |
      | modalmodules           | page     | format_muonline |
      | modalresources         | pdf,html | format_muonline |
      | assumedatastoreconsent | 1        | format_muonline |
      | reopenlastsection      | 0        | format_muonline |
      | usejavascriptnav       | 1        | format_muonline |

  @javascript
  Scenario: Open page using modal as student with submuonline on
    When format_muonline submuonline are on for course "Course 5"
    And I log in as "student1"
    And I am on "Course 5" course homepage
    And I wait until the page is ready
    And I click on tile "6"
    And I wait until the page is ready
    And I wait "1" seconds
    And I click format muonline activity "Test page name"
    And I wait until the page is ready
    And "Test page name" "dialogue" should be visible
    And "Test page content" "text" should be visible
    And "Close" "button" should exist in the "Test page name" "dialogue"
    And I click on "Close" "button" in the "Test page name" "dialogue"
    And I wait until the page is ready
    And I wait "1" seconds

    And I click on close button for tile "6"
    And "Test page content" "text" should not be visible
    And I log out muonline

  @javascript
  Scenario: Open page using modal as student - with submuonline off
    When format_muonline submuonline are off for course "Course 5"
    And I log in as "student1"
    And I am on "Course 5" course homepage
    And I click on tile "6"
    And I wait until the page is ready
    And I click format muonline activity "Test page name"
    And I wait until the page is ready
    And "Test page name" "dialogue" should be visible
    And "Test page content" "text" should be visible
    And "Close" "button" should exist in the "Test page name" "dialogue"
    And I click on "Close" "button" in the "Test page name" "dialogue"
    And I wait until the page is ready
    And I wait "1" seconds
    And I click on close button for tile "6"
    And "Test page content" "text" should not be visible
    And I log out muonline
