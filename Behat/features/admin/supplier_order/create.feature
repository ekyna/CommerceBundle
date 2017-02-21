@commerce
Feature: Create supplier orders
    In order to manage products supply
    As an administrator
    I need to be able to create new supplier orders

    Background:
        Given I am logged in as an administrator
        And The following suppliers:
            | name     | currency | email                | gender | lastName | firstName |
            | TechData | EUR      | contact@techdata.com | mr     | Dupont   | Jean      |

    @supplier-order
    Scenario: Create a supplier order
        When I go to "ekyna_commerce_supplier_order_admin_new" route
        And I select "TechData" from "supplier_order[supplier]"
        And I press "form_flow_submit"
        #And I fill in "attribute[name]" with "Noir"
        And I press "form_flow_submit"
        Then I should see the resource saved confirmation message
        And I should see "TechData"
        # And I should see "TechData" TODO order number

    # TODO
    # - set items
    # - set items from order products
    # - test total
