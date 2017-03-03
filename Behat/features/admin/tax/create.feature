@commerce @tax
Feature: Create taxes
    In order to manage sales
    As an administrator
    I need to be able to create new taxes

    Background:
        Given I am logged in as an administrator

    Scenario: Create a tax
        When I go to "ekyna_commerce_tax_admin_new" route
        And I fill in "tax[name]" with "TVA 20%"
        And I fill in "tax[rate]" with "20"
        And I select "France" from "tax[country]"
        And I press "tax_actions_save"
        Then I should see the resource saved confirmation message
        And I should see "TVA 20%" in the "#tax_name" element
        And I should see "20,00 %" in the "#tax_rate" element
        And I should see "France" in the "#tax_country" element

        # TODO
        # - postalCodeMatch
