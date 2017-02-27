@commerce @supplier
Feature: Edit suppliers
    In order to manage products supply
    As an administrator
    I need to be able to edit suppliers

    Background:
        Given I am logged in as an administrator
        And The following suppliers:
            | name     | currency | email                | gender | lastName | firstName |
            | TechData | EUR      | contact@techdata.com | mr     | Dupont   | Jean      |

    Scenario: Edit the supplier
        When I go to "ekyna_commerce_supplier_admin_edit" route with "supplierId:1"
        And I fill in "supplier[name]" with "Ingram"
        And I fill in "supplier[email]" with "contact@ingram.com"
        And I select "Mme" from "supplier[identity][gender]"
        And I fill in "supplier[identity][lastName]" with "Marie"
        And I fill in "supplier[identity][firstName]" with "Jeanne"
        # TODO address ?
        And I press "supplier_actions_save"
        Then I should see the resource saved confirmation message
        And I should see "Ingram"
