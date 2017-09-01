@commerce @stock @supplier-delivery
Feature: Create supplier deliveries
    In order to manage products supply
    As an administrator
    I need to be able to submit supplier deliveries

    Background:
        Given I am logged in as an administrator
        And The following acme products:
            | designation | reference | price     | weight |
            | iPad Air    | IPAD-AIR  | 266.66667 | 0.8    |
            | Galaxy Tab  | GALA-TAB  | 249.16667 | 0.7    |
        And The following suppliers:
            | name     | currency | email                | gender | lastName | firstName |
            | TechData | EUR      | contact@techdata.com | mr     | Dupont   | Jean      |
        And The following supplier products:
            | supplier | designation | reference | price     | weight | available | ordered | eda | acme_product |
            | TechData | iPad Air    | IPAD-AIR  | 249.16667 | 0.8    | 40        | 0       |     | IPAD-AIR     |
            | TechData | Galaxy Tab  | GALA-TAB  | 207.5     | 0.7    | 20        | 0       |     | GALA-TAB     |
        And The following supplier orders:
            | number | supplier | currency | shippingCost | eda |
            | SO-001 | TechData | EUR      | 20.55        | 2020-01-01 |
        And The following supplier order items:
            | order  | reference | quantity |
            | SO-001 | IPAD-AIR  | 2        |
            | SO-001 | GALA-TAB  | 4        |
        And The supplier order with number "SO-001" is submitted

    @javascript
    Scenario: Create the supplier partial delivery
        When I go to "ekyna_commerce_supplier_delivery_admin_new" route with "supplierOrderId:1"
        And I fill in "supplier_delivery[items][0][quantity]" with "0"
        And I fill in "supplier_delivery[items][1][quantity]" with "2"
        And I press "supplier_delivery[actions][save]"
        Then I should see the resource saved confirmation message
        And I should not see "Soumettre au fournisseur"

        # Order assertions
        And I should see "TechData" in the "#order_supplier" element
        And I should see "20,55" in the "#order_shippingCost" element
        And I should see "1 348,89" in the "#order_paymentTotal" element
        And I should see "Partiellement réceptionnée" in the "#order_state" element
        And I should see "01/01/2020" in the "#order_estimatedDateOfArrival" element

        # Items assertions
#        And I should see "iPad Air" in the "#item_0_designation" element
#        And I should see "IPAD-AIR" in the "#item_0_reference" element
#        And I should see "2" in the "#item_0_quantity" element
#        And I should see "249,17" in the "#item_0_netPrice" element
#        And I should see "iPad Air" in the "#item_0_subject" element
#
#        And I should see "Galaxy Tab" in the "#item_1_designation" element
#        And I should see "GALA-TAB" in the "#item_1_reference" element
#        And I should see "4" in the "#item_1_quantity" element
#        And I should see "207,50" in the "#item_1_netPrice" element
#        And I should see "Galaxy Tab" in the "#item_1_subject" element

        # Deliveries assertions
        And I show the "deliveries" tab
        And I should see "2" in the "#delivery_0_item_0_quantity" element

        # Product assertions
        When I go to "acme_product_product_admin_show" route with "productId:1"
        Then I should see "Pré-commande" in the "#product_stockState" element
        Then I should see "0" in the "#product_inStock" element
        Then I should see "2" in the "#product_virtualStock" element
        Then I should see "01/01/2020" in the "#product_estimatedDateOfArrival" element
        Then I should see "En attente" in the "#product_stockUnit_0_state" element
        Then I should see "2" in the "#product_stockUnit_0_orderedQuantity" element
        Then I should see "0" in the "#product_stockUnit_0_receivedQuantity" element

        When I go to "acme_product_product_admin_show" route with "productId:2"
        Then I should see "En stock" in the "#product_stockState" element
        Then I should see "2" in the "#product_inStock" element
        Then I should see "4" in the "#product_virtualStock" element
        Then I should see "01/01/2020" in the "#product_estimatedDateOfArrival" element
        Then I should see "Prête" in the "#product_stockUnit_0_state" element
        Then I should see "4" in the "#product_stockUnit_0_orderedQuantity" element
        Then I should see "2" in the "#product_stockUnit_0_receivedQuantity" element

    @javascript @stock
    Scenario: Create the supplier complete delivery
        When I go to "ekyna_commerce_supplier_delivery_admin_new" route with "supplierOrderId:1"
        And I fill in "supplier_delivery[items][0][quantity]" with "2"
        And I fill in "supplier_delivery[items][1][quantity]" with "4"
        And I press "supplier_delivery[actions][save]"
        Then I should see the resource saved confirmation message

        # Order assertions
        # TODO And I should not see "Soumettre au fournisseur"
        And I should see "TechData" in the "#order_supplier" element
        And I should see "20,55" in the "#order_shippingCost" element
        And I should see "1 348,89" in the "#order_paymentTotal" element
        And I should see "Complétée" in the "#order_state" element
        And I should see "01/01/2020" in the "#order_estimatedDateOfArrival" element

        # Items assertions
        And I should see "iPad Air" in the "#item_0_designation" element
        And I should see "IPAD-AIR" in the "#item_0_reference" element
        And I should see "2" in the "#item_0_quantity" element
        And I should see "249,17" in the "#item_0_netPrice" element
        And I should see "iPad Air" in the "#item_0_subject" element

        And I should see "Galaxy Tab" in the "#item_1_designation" element
        And I should see "GALA-TAB" in the "#item_1_reference" element
        And I should see "4" in the "#item_1_quantity" element
        And I should see "207,50" in the "#item_1_netPrice" element
        And I should see "Galaxy Tab" in the "#item_1_subject" element

        # Deliveries assertions
        And I show the "deliveries" tab
        And I should see "2" in the "#delivery_0_item_0_quantity" element
        And I should see "4" in the "#delivery_0_item_1_quantity" element

        # Product assertions
        When I go to "acme_product_product_admin_show" route with "productId:1"
        Then I should see "En stock" in the "#product_stockState" element
        Then I should see "2" in the "#product_inStock" element
        Then I should see "2" in the "#product_virtualStock" element
        Then I should see "Indéfini" in the "#product_estimatedDateOfArrival" element
        Then I should see "Prête" in the "#product_stockUnit_0_state" element
        Then I should see "2" in the "#product_stockUnit_0_orderedQuantity" element
        Then I should see "2" in the "#product_stockUnit_0_receivedQuantity" element

        When I go to "acme_product_product_admin_show" route with "productId:2"
        Then I should see "En stock" in the "#product_stockState" element
        Then I should see "4" in the "#product_inStock" element
        Then I should see "4" in the "#product_virtualStock" element
        Then I should see "Indéfini" in the "#product_estimatedDateOfArrival" element
        Then I should see "Prête" in the "#product_stockUnit_0_state" element
        Then I should see "4" in the "#product_stockUnit_0_orderedQuantity" element
        Then I should see "4" in the "#product_stockUnit_0_receivedQuantity" element
