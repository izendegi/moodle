@quiz @quiz_heartbeat @javascript
Feature: Correct handling of groups

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "users" exist:
      | username | firstname | lastname |
      | teacher1 | T1        | Teacher1 |
      | teacher2 | T2        | Teacher2 |
      | teacher3 | T3        | Teacher2 |
      | student1 | S1        | Student1 |
      | student2 | S2        | Student2 |
      | student3 | S3        | Student3 |
      | student4 | S4        | Student4 |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | teacher2 | C1     | teacher        |
      | teacher3 | C1     | teacher        |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
      | student3 | C1     | student        |
      | student4 | C1     | student        |
    And the following "groups" exist:
      | name   | course | idnumber |
      | group1 | C1     | G1       |
      | group2 | C1     | G2       |
      | group3 | C1     | G3       |
      | group4 | C1     | G4       |
      | group5 | C1     | G5       |
    And the following "group members" exist:
      | user     | group |
      | teacher2 | G1    |
      | teacher3 | G1    |
      | teacher3 | G2    |
      | student1 | G1    |
      | student2 | G2    |
      | student3 | G2    |
      | student3 | G3    |
      | student4 | G4    |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "activities" exist:
      | activity | name   | intro              | course | groupmode |
      | quiz     | Quiz 1 | Quiz 1 description | C1     | 1         |
    And the following "questions" exist:
      | questioncategory | qtype | name | questiontext   |
      | Test questions   | essay | Q1   | First question |
    And quiz "Quiz 1" contains the following questions:
      | question | page | maxmark |
      | Q1       | 1    | 1.0     |
    And user "student1" has started an attempt at quiz "Quiz 1"
    And user "student1" has input answers in their attempt at quiz "Quiz 1":
      | slot | response |
      | 1    | Foo Bar  |
    And user "student2" has started an attempt at quiz "Quiz 1"
    And user "student2" has input answers in their attempt at quiz "Quiz 1":
      | slot | response  |
      | 1    | Foo Bar 2 |
    And user "student3" has started an attempt at quiz "Quiz 1"
    And user "student3" has input answers in their attempt at quiz "Quiz 1":
      | slot | response  |
      | 1    | Foo Bar 3 |

  Scenario: An editing teacher should see all students and all groups.
    When I am on the "Quiz 1" "quiz_heartbeat > heartbeat report" page logged in as "teacher1"
    Then I should see "Separate groups"
    And I should see "Attempts: 3"
    And the following should exist in the "heartbeatoverview" table:
      | -1-         |
      | S1 Student1 |
      | S2 Student2 |
      | S3 Student3 |
    And "group" "field" should exist
    And the "group" select box should contain "group1"
    And the "group" select box should contain "group2"
    And the "group" select box should contain "group3"
    When I set the field "group" to "group3"
    Then I should see "Attempts: 3 (1 from this group)"
    And the following should exist in the "heartbeatoverview" table:
      | -1-         |
      | S3 Student3 |
    And the following should not exist in the "heartbeatoverview" table:
      | -1-         |
      | S1 Student2 |
      | S2 Student2 |
      | S4 Student4 |

  Scenario: If a (non-editing) teacher is only in one group, they should not see the group selection dropdown.
    When I am on the "Quiz 1" "quiz_heartbeat > heartbeat report" page logged in as "teacher2"
    Then I should see "Separate groups: group1"
    And I should see "Attempts: 3 (1 from this group)"
    And "group" "field" should not exist
    And the following should exist in the "heartbeatoverview" table:
      | -1-         |
      | S1 Student1 |
    And the following should not exist in the "heartbeatoverview" table:
      | -1-         |
      | S2 Student2 |
      | S3 Student3 |
      | S4 Student4 |

  Scenario: A (non-editing) teacher should only have access to their groups.
    When I am on the "Quiz 1" "quiz_heartbeat > heartbeat report" page logged in as "teacher3"
    Then I should see "Separate groups"
    And "group" "field" should exist
    And I should see "Attempts: 3 (1 from this group)"
    And the "group" select box should not contain "group3"
    When I set the field "group" to "group2"
    Then I should see "Attempts: 3 (2 from this group)"
    And the following should exist in the "heartbeatoverview" table:
      | -1-         |
      | S2 Student2 |
      | S3 Student3 |
    And the following should not exist in the "heartbeatoverview" table:
      | -1-         |
      | S1 Student1 |
      | S4 Student4 |

  Scenario: If there are students in a group, but none attempted the quiz, the user should see an information message.
    When I am on the "Quiz 1" "quiz_heartbeat > heartbeat report" page logged in as "teacher1"
    Then I should see "Separate groups"
    When I set the field "group" to "group4"
    Then I should see "Attempts: 3 (0 from this group)"
    And I should see "Nothing to display"

  Scenario: If there are no students in a group, the user should see an information message.
    When I am on the "Quiz 1" "quiz_heartbeat > heartbeat report" page logged in as "teacher1"
    Then I should see "Separate groups"
    When I set the field "group" to "group5"
    Then I should see "Attempts: 3 (0 from this group)"
    And I should see "There are no students in this group yet"
    And I should see "Nothing to display"
