@commerce
Feature: Remove suppliers
    In order to manage products supply
    As an administrator
    I need to be able to remove suppliers

    Background:
        Given I am logged in as an administrator
        And The following suppliers:
            | name     | currency | email                | gender | lastName | firstName |
            | TechData | EUR      | contact@techdata.com | mr     | Dupont   | Jean      |

    Scenario: Remove the supplier
        When I go to "ekyna_commerce_supplier_admin_remove" route with "supplierId:1"
        And I check "form[confirm]"
        And I press "form[actions][remove]"
        Then I should see the resource removed confirmation message
        And I should not see "TechData"

    # TODO Scenario: Remove a supplier used by supplier order (should be prevented)
