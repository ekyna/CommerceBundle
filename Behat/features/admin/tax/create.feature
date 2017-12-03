@commerce @tax
Feature: Create taxes
    In order to manage sales
    As an administrator
    I need to be able to create new taxes

    Background:
        Given I am logged in as an administrator

    Scenario: Create a tax
        When I go to "ekyna_commerce_tax_admin_new" route
        And I fill in "tax[name]" with "Test tax"
        And I fill in "tax[rate]" with "10"
        And I select "France" from "tax[country]"
        And I press "tax_actions_save"
        Then I should see the resource saved confirmation message
        And I should see "Test tax" in the "#tax_name" element
        And I should see "10 %" in the "#tax_rate" element
        And I should see "France" in the "#tax_country" element

        # TODO
        # - postalCodeMatch
