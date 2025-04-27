@local @local_faultreporting @javascript
Feature: Basic tests for Fault Reporting

  Scenario: Admin "Fault reports" page is available
    Given I log in as "admin"
    When I follow "Site administration"
    When I follow "Plugins"
    And I follow "Fault Reporting"
    And I follow "Fault reports"
    Then I should see "No fault reports"

  Scenario: As an Admin I can submit a fault report
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Andrew    | Barry    | student1@example.com |
    And I log in as "student1"
    And I follow "Home"
    And I follow "Fault Reporting"
    Then I should see "Create new fault report"
    And I set the following fields to these values:
      | Description | test1 |
    And I press "Submit report"
    Then I should see "Well, this is embrassing"
    Given I log in as "admin"
    When I follow "Site administration"
    When I follow "Plugins"
    And I follow "Fault Reporting"
    And I follow "Fault reports"
    Then I should see "test1"
