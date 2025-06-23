@local @local_faultreporting @javascript
Feature: Basic tests for Fault Reporting

  Background:
    Given I log in as "admin"
    And I set the following administration settings values:
      | customusermenuitems | pluginname,local_faultreporting\|/local/faultreporting/faultreport.php |

    Given the following "users" exist:
    | username | firstname | lastname | email                |
    | 98186700 | Andrew    | Barry    | student1@example.com |
    | st100585 | Andrew    | Steve    | st1@example.com      |

  Scenario: Admin "Fault reports" page is available
    Given I am on the "local_faultreporting > faultreports" page logged in as "admin"
    Then I should see "No fault reports"

  Scenario: As a user I can submit a fault report
    Given I am on the "local_faultreporting > faultreport" page logged in as "98186700"
    Then I should see "Create new fault report"
    And I should see "Something not working quite right with Stream? Use this form to log a support request. Remember to include as much information as possible."
    And I set the following fields to these values:
      | Description | test98186700 |
    And I press "Submit report"
    Then I should see "Report successfully queued for sending"

    Given I am on the "local_faultreporting > faultreport" page logged in as "st100585"
    And I should see "Something not working quite right with Stream? Use this form to log a support request. Remember to include as much information as possible."
    And I should see "Please ensure that your Email address is correct."
    Then I should see "Create new fault report"
    And I set the following fields to these values:
      | Description | testst100585 |
    And I press "Submit report"
    Then I should see "Report successfully queued for sending"

    Given I log in as "admin"
    And I follow "Fault Reporting" in the user menu
    And I should see "Something not working quite right with Stream? Use this form to log a support request. Remember to include as much information as possible."
    And I should see "While you can use this form, as a staff member you are encouraged to"

    Given I am on the "local_faultreporting > faultreports" page logged in as "admin"
    Then I should see "test98186700"
    Then I should see "testst100585"
