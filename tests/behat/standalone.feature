@local @local_faultreporting
Feature: The fault report form works without JavaScript
  In order to report a fault
  As a user without JavaScript
  I need the standalone fault report page to keep working

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | 98186700 | Andrew    | Barry    | student1@example.com |

  Scenario: A user can submit a fault report from the standalone page
    Given I am on the "local_faultreporting > faultreport" page logged in as "98186700"
    Then I should see "Create new fault report"
    And I should see "Something not working quite right with Stream? Use this form to log a support request. Remember to include as much information as possible."
    And I should see "student1@example.com"
    When I set the field "Description" to "test98186700 standalone"
    And I press "Submit report"
    Then I should see "Report successfully queued for sending"

    Given I am on the "local_faultreporting > faultreports" page logged in as "admin"
    Then I should see "test98186700 standalone"

  Scenario: A description is required
    Given I am on the "local_faultreporting > faultreport" page logged in as "98186700"
    When I press "Submit report"
    Then I should see "Create new fault report"
    And I should not see "Report successfully queued for sending"
