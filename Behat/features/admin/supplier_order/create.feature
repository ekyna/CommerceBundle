@commerce @supply @supplier-order
Feature: Create supplier orders
    In order to manage products supply
    As an administrator
    I need to be able to create new supplier orders

    Background:
        Given I am logged in as an administrator
        And The following acme products:
            | designation | reference | price | weight |
            | iPad Air    | IPAD-AIR  | 290   | 0.8    |
        And The following supplier carriers:
            | name |
            | TNT  |
        And The following suppliers:
            | name     | currency | carrier | email                | gender | lastName | firstName |
            | TechData | EUR      | TNT     | contact@techdata.com | mr     | Dupont   | Jean      |
        And The following supplier products:
            | supplier | designation | reference | price     | weight | available | ordered | eda  | acme_product |
            | TechData | iPad Air    | IPAD-AIR  | 249.16667 | 0.8    | 40        | 0       |      | IPAD-AIR     |

    @javascript
    Scenario: Create a supplier order
        When I go to "ekyna_commerce_supplier_order_admin_new" route
        And I select "TechData" from "supplier_order[supplier]"
        And I press "form_flow_submit"
        And I select "Euro" from "supplier_order[currency]"
        And I fill in "supplier_order[shippingCost]" with "30"
        And I wait for Select2 initialization on "supplier_order[quickAddSelect]"
        And I select "[IPAD-AIR] iPad Air" from "supplier_order[quickAddSelect]"
        And I press "supplier_order[quickAddButton]"
        And I press "form_flow_submit"

        Then I should see the resource saved confirmation message
        # Order assertions
        And I should see "TechData" in the "#order_supplier" element
        And I should see "30,00 €" in the "#order_shippingCost" element
        And I should see "279,17 €" in the "#order_paymentTotal" element
        And I should see "Création" in the "#order_state" element
        And I should see "Soumettre au fournisseur"

        # Items assertions
        And I should see "iPad Air" in the "#item_0_designation" element
        And I should see "IPAD-AIR" in the "#item_0_reference" element
        And I should see "1" in the "#item_0_quantity" element
        And I should see "249,17" in the "#item_0_netPrice" element
        And I should see "iPad Air" in the "#item_0_subject" element

        # Deliveries
        And I show the "deliveries" tab
        And I should not see "Nouvelle livraison"
        And I should see "Aucune livraison fournisseur configuré"
        # TODO order number
        # TODO order paymentDate
        # TODO order estimatedDateOfArrival
