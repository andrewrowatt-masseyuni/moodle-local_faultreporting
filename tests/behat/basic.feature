@local @local_faultreporting
Feature: Basic tests for Fault Reporting

  Scenario: Admin "Fault reports" page is available
    Given I log in as "admin"
    When I navigate to "Plugins" in site administration
    And I follow "Fault Reporting"
    And I follow "Fault Reports"
    Then I should see "No fault reports"

  Scenario: As an Admin I can submit a fault report
    Given I log in as "admin"
    And I follow "Fault Reporting"
    Then I should see "Create new fault report"
