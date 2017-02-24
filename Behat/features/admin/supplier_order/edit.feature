@commerce
Feature: Edit supplier orders
    In order to manage products supply
    As an administrator
    I need to be able to edit supplier orders

    Background:
        Given I am logged in as an administrator
        And The following suppliers:
            | name     | currency | email                | gender | lastName | firstName |
            | TechData | EUR      | contact@techdata.com | mr     | Dupont   | Jean      |
        And The following supplier orders:
            | number | supplier | currency | paymentTotal |
            | SO-001 | TechData | EUR      | 249.16667    |

    @supplier-order
    Scenario: Edit the supplier order
        When I go to "ekyna_commerce_supplier_order_admin_edit" route with "supplierOrderId:1"
        And I fill in "supplier_order[estimatedDateOfArrival]" with "01/01/2020"
        And I press "supplier_order_actions_save"
        Then I should see the resource saved confirmation message
        And I should see "01/01/2020" in the "#order_estimatedDateOfArrival" element

    # TODO
    # - change items and test total
    # - Editing submitted orders should be prevented
    # - stock units impact
