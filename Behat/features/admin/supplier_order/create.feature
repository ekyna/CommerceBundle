@commerce @supplier-order
Feature: Create supplier orders
    In order to manage products supply
    As an administrator
    I need to be able to create new supplier orders

    Background:
        Given I am logged in as an administrator
        And The following acme products:
            | designation | reference | price | weight |
            | iPad Air    | IPAD-AIR  | 290   | 0.8    |
        And The following suppliers:
            | name     | currency | email                | gender | lastName | firstName |
            | TechData | EUR      | contact@techdata.com | mr     | Dupont   | Jean      |
        And The following supplier products:
            | supplier | designation | reference | price     | weight | available | ordered | eda  | acme_product |
            | TechData | iPad Air    | IPAD-AIR  | 249.16667 | 0.8    | 40        | 0       |      | IPAD-AIR     |

    @javascript
    Scenario: Create a supplier order
        When I go to "ekyna_commerce_supplier_order_admin_new" route
        And I select "TechData" from "supplier_order[supplier]"
        And I press "form_flow_submit"
        And I select "Euro" from "supplier_order[currency]"
        And I fill in "supplier_order[paymentTotal]" with "249.16667"
        And I wait for Select2 initialization on "supplier_order[compose][quickAddSelect]"
        And I select "[IPAD-AIR] iPad Air" from "supplier_order[compose][quickAddSelect]"
        And I press "supplier_order[compose][quickAddButton]"
        And I press "form_flow_submit"

        Then I should see the resource saved confirmation message
        # Order assertions
        And I should see "TechData" in the "#order_supplier" element
        And I should see "249,17 €" in the "#order_paymentTotal" element
        And I should see "Création" in the "#order_state" element
        # TODO order number
        # TODO order paymentDate
        # TODO order estimatedDateOfArrival

    # TODO
    # - set items
    # - set items from order products
    # - test total
