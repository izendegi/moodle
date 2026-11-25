@format @format_muonline @format_muonline_create_course
Feature: Create course in format muonline
  As an admin
  I need to be able to create a course in format muonline and set the options

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
    And the following config values are set as admin:
      | config                 | value    | plugin       |
      | enablecompletion       | 1        | core         |
      | modalmodules           | page     | format_muonline |
      | modalresources         | pdf,html | format_muonline |
      | assumedatastoreconsent | 1        | format_muonline |
      | reopenlastsection      | 0        | format_muonline |
      | usejavascriptnav       | 1        | format_muonline |

  @javascript
  Scenario: Latest course announcements are displayed and can be configured
    When I log in as "admin"

    And I create a course with:
      | Course full name | Create Course Test Course |
      | Course short name | C1 |
      | Number of announcements | 5 |
      | Enable completion tracking | Yes |
      | Format | Muonline |
      | Number of sections | 10 |
      | Use sub muonline for activities | Yes |
      | Progress on each tile | Show as % |

    And I am on "Create Course Test Course" course homepage with editing mode on
    And I log out muonline

    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And I log in as "teacher1"
    And I am on "Create Course Test Course" course homepage with editing mode on
    And I log out muonline
