@commerce @stock @supplier-order
Feature: Submit supplier orders
    In order to manage products supply
    As an administrator
    I need to be able to submit supplier orders

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
        And The following supplier orders:
            | number | supplier | currency | shippingCost | eda        |
            | SO-001 | TechData | EUR      | 30           | 2020-01-01 |
        And The following supplier order items:
            | order  | reference | quantity |
            | SO-001 | IPAD-AIR  | 2        |

    Scenario: Submit the supplier order
        When I go to "ekyna_commerce_supplier_order_admin_submit" route with "supplierOrderId:1"
        And I fill in "supplier_order_submit[order][estimatedDateOfArrival]" with "01/01/2020"
        # TODO message field
        And I check "supplier_order_submit[confirm]"
        And I press "supplier_order_submit_actions_send"
        Then I should see the resource saved confirmation message

        # TODO Email sent assertion

        # Order assertions
        And I should see "TechData" in the "#order_supplier" element
        And I should see "30,00 €" in the "#order_shippingCost" element
        And I should see "528,34 €" in the "#order_paymentTotal" element
        And I should see "En attente" in the "#order_state" element
        And I should see "01/01/2020" in the "#order_estimatedDateOfArrival" element
        # TODO And I should not see "Soumettre au fournisseur"

        # Items assertions
        And I should see "iPad Air" in the "#item_0_designation" element
        And I should see "IPAD-AIR" in the "#item_0_reference" element
        And I should see "2" in the "#item_0_quantity" element
        And I should see "249,17" in the "#item_0_netPrice" element
        And I should see "iPad Air" in the "#item_0_subject" element

        # Product assertions
        When I go to "acme_product_product_admin_show" route with "productId:1"
        Then I should see "Pré-commande" in the "#product_stockState" element
        Then I should see "0" in the "#product_inStock" element
        Then I should see "2" in the "#product_virtualStock" element
        Then I should see "01/01/2020" in the "#product_estimatedDateOfArrival" element

        # Stock units assertions
        Then I should see "En attente" in the "#product_stockUnit_0_state" element
        Then I should see "2" in the "#product_stockUnit_0_orderedQuantity" element
        Then I should see "0" in the "#product_stockUnit_0_receivedQuantity" element
        Then I should see "01/01/2020" in the "#product_stockUnit_0_estimatedDateOfArrival" element
