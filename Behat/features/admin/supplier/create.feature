@commerce @supplier
Feature: Create suppliers
    In order to manage products supply
    As an administrator
    I need to be able to create new suppliers

    Background:
        Given I am logged in as an administrator

    Scenario: Create a supplier
        When I go to "ekyna_commerce_supplier_admin_new" route
        And I fill in "supplier[name]" with "TechData"
        And I fill in "supplier[email]" with "contact@techdata.com"
        And I select "Mr" from "supplier[identity][gender]"
        And I fill in "supplier[identity][lastName]" with "Dupont"
        And I fill in "supplier[identity][firstName]" with "Jean"
        # TODO address ?
        And I press "supplier_actions_save"
        Then I should see the resource saved confirmation message
        And I should see "TechData"

    # TODO
    # - SupplierAddress
