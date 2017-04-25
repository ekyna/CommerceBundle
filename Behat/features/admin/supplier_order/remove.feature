@commerce @stock @supplier-order
Feature: Remove supplier orders
    In order to manage products supply
    As an administrator
    I need to be able to remove supplier orders

    Background:
        Given I am logged in as an administrator
        And The following suppliers:
            | name     | currency | email                | gender | lastName | firstName |
            | TechData | EUR      | contact@techdata.com | mr     | Dupont   | Jean      |
        And The following supplier orders:
            | number | supplier | currency |
            | SO-001 | TechData | EUR      |

    Scenario: Remove the supplier order
        When I go to "ekyna_commerce_supplier_order_admin_remove" route with "supplierOrderId:1"
        And I check "form[confirm]"
        And I press "form[actions][remove]"
        Then I should see the resource removed confirmation message
        And I should not see "SO-001"

    # TODO
    # - Removing a submitted order should be prevented
    # - stock impact
