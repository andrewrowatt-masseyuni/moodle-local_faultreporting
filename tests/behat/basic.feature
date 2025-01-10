@local @local_faultreporting
Feature: Basic tests for Fault Reporting

  @javascript
  Scenario: Plugin local_faultreporting appears in the list of installed additional plugins
    Given I log in as "admin"
    When I navigate to "Plugins > Plugins overview" in site administration
    And I follow "Additional plugins"
    Then I should see "Fault Reporting"
    And I should see "local_faultreporting"
