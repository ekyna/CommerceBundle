@commerce @order-shipment
Feature: Create order shipments
    In order to deliver products
    As an administrator
    I need to be able to create new order shipments

    Background:
        Given I am logged in as an administrator
        And The following shipment zones:
            | name         | countries |
            | France métro | FR        |
        And The following shipment methods:
            | name |
            | GLS  |
        And The following shipment prices:
            | zone          | method | weight | price |
            | France métro  | GLS    | 1      | 5.2   |
            | France métro  | GLS    | 2      | 8.5   |
        And The following customers:
            | email              | company        | gender | lastName | firstName |
            | contact@dupont.com | Dupont et fils | mr     | Dupont   | Jean      |

        And The following acme products:
            | designation | reference | price     | weight |
            | iPad Air    | IPAD-AIR  | 266.66667 | 0.8    |
            | Galaxy Tab  | GALA-TAB  | 249.16667 | 0.7    |
        And The following suppliers:
            | name     | currency | email                | gender | lastName | firstName |
            | TechData | EUR      | contact@techdata.com | mr     | Dupont   | Jean      |
        And The following supplier products:
            | supplier | designation | reference | price     | weight | available | ordered | acme_product |
            | TechData | iPad Air    | IPAD-AIR  | 249.16667 | 0.8    | 40        | 0       | IPAD-AIR     |
            | TechData | Galaxy Tab  | GALA-TAB  | 207.5     | 0.7    | 20        | 0       | GALA-TAB     |
        And The following supplier orders:
            | number | supplier | currency | paymentTotal | estimatedDateOfArrival |
            | SO-001 | TechData | EUR      | 1328.33334   | 2020-01-01             |
        And The following supplier order items:
            | order  | reference | quantity |
            | SO-001 | IPAD-AIR  | 6        |
            | SO-001 | GALA-TAB  | 2        |
        And The supplier order with number "SO-001" is submitted

        And The following orders:
            | number | customer           | street            | postalCode | city   | shipmentMethod |
            | O-0001 | contact@dupont.com | 10 rue de la soif | 35000      | Rennes | GLS            |
        And The following order items:
            | order  | quantity | acme_product |
            | O-0001 | 4        | IPAD-AIR     |
            | O-0001 | 2        | GALA-TAB     |

    @javascript @stock
    Scenario: Create a shipment
        Given The supplier order with number "SO-001" is received
        And The order with number "O-0001" is paid

        When I go to "ekyna_commerce_order_shipment_admin_new" route with "{orderId:1}"
        And I fill in "order_shipment[items][0][quantity]" with "4"
        And I fill in "order_shipment[items][1][quantity]" with "2"
        And I press "order_shipment[actions][save]"
        Then I should see the resource saved confirmation message

        # Shipment assertions
        When I show the "shipments" tab
        Then I should see "Non" in the "#shipment_0_return" element
        And I should see "GLS" in the "#shipment_0_method" element
        And I should see "Création" in the "#shipment_0_state" element

        When I click "shipment_0_toggle_details"
        Then I should see "4" in the "#shipment_0_item_0_quantity" element
        And I should see "2" in the "#shipment_0_item_1_quantity" element

        # Product #1 assertions
        When I go to "acme_product_product_admin_show" route with "productId:1"
        Then I should see "6" in the "#product_inStock" element
        And I should see "En stock" in the "#product_stockState" element
        And I should see "Prête" in the "#product_stockUnit_0_state" element
        And I should see "6" in the "#product_stockUnit_0_orderedQuantity" element
        And I should see "6" in the "#product_stockUnit_0_receivedQuantity" element
        And I should see "4" in the "#product_stockUnit_0_soldQuantity" element
        And I should see "0" in the "#product_stockUnit_0_shippedQuantity" element

        # Product #2 assertions
        When I go to "acme_product_product_admin_show" route with "productId:2"
        Then I should see "2" in the "#product_inStock" element
        And I should see "En rupture" in the "#product_stockState" element
        And I should see "Prête" in the "#product_stockUnit_0_state" element
        And I should see "2" in the "#product_stockUnit_0_orderedQuantity" element
        And I should see "2" in the "#product_stockUnit_0_receivedQuantity" element
        And I should see "2" in the "#product_stockUnit_0_soldQuantity" element
        And I should see "0" in the "#product_stockUnit_0_shippedQuantity" element

#    Can't test this as 'shipped' state is available in state select field.
#    @javascript
#    Scenario: Create a shipped shipment while order is not accepted
#        When I go to "ekyna_commerce_order_shipment_admin_new" route with "{orderId:1}"
#        And I select "Expédié" from "order_shipment[state]"
#        And I fill in "order_shipment[items][0][quantity]" with "4"
#        And I fill in "order_shipment[items][1][quantity]" with "2"
#        And I press "order_shipment[actions][save]"
#        Then I should see "Le statut de la vente ne permet pas de sélectionner ce statut d'expédition"

    @javascript @stock
    Scenario: Create a shipment with state 'shipped'
        Given The supplier order with number "SO-001" is received
        And The order with number "O-0001" is paid

        When I go to "ekyna_commerce_order_shipment_admin_new" route with "{orderId:1}"
        And I select "Expédié" from "order_shipment[state]"
        And I fill in "order_shipment[items][0][quantity]" with "4"
        And I fill in "order_shipment[items][1][quantity]" with "2"
        And I press "order_shipment[actions][save]"
        Then I should see the resource saved confirmation message

        # Shipment assertions
        When I show the "shipments" tab
        Then I should see "Non" in the "#shipment_0_return" element
        And I should see "GLS" in the "#shipment_0_method" element
        And I should see "Expédié" in the "#shipment_0_state" element

        When I click "shipment_0_toggle_details"
        Then I should see "4" in the "#shipment_0_item_0_quantity" element
        And I should see "2" in the "#shipment_0_item_1_quantity" element

        # Product #1 assertions
        When I go to "acme_product_product_admin_show" route with "productId:1"
        Then I should see "2" in the "#product_inStock" element
        And I should see "En stock" in the "#product_stockState" element
        And I should see "Prête" in the "#product_stockUnit_0_state" element
        And I should see "6" in the "#product_stockUnit_0_orderedQuantity" element
        And I should see "6" in the "#product_stockUnit_0_receivedQuantity" element
        And I should see "4" in the "#product_stockUnit_0_soldQuantity" element
        And I should see "4" in the "#product_stockUnit_0_shippedQuantity" element

        # Product #2 assertions
        When I go to "acme_product_product_admin_show" route with "productId:2"
        Then I should see "0" in the "#product_inStock" element
        And I should see "Aucune unité de stock disponible"

    @javascript @stock
    Scenario: Create a return shipment with state 'shipped'
        Given The supplier order with number "SO-001" is received
        And The order with number "O-0001" is paid
        And The order with number "O-0001" is shipped

        When I go to "ekyna_commerce_order_shipment_admin_new" route with "{orderId:1,return:1}"
        And I select "Expédié" from "order_shipment[state]"
        And I fill in "order_shipment[items][0][quantity]" with "4"
        And I fill in "order_shipment[items][1][quantity]" with "2"
        And I press "order_shipment[actions][save]"
        Then I should see the resource saved confirmation message

        # Shipment assertions
        When I show the "shipments" tab
        Then I should see "Oui" in the "#shipment_1_return" element
        #And I should see "GLS" in the "#shipment_1_method" element
        And I should see "Expédié" in the "#shipment_1_state" element

        When I click "shipment_1_toggle_details"
        Then I should see "4" in the "#shipment_1_item_0_quantity" element
        And I should see "2" in the "#shipment_1_item_1_quantity" element

        # Product #1 assertions
        When I go to "acme_product_product_admin_show" route with "productId:1"
        Then I should see "6" in the "#product_inStock" element
        And I should see "En stock" in the "#product_stockState" element
        And I should see "Prête" in the "#product_stockUnit_0_state" element
        And I should see "6" in the "#product_stockUnit_0_orderedQuantity" element
        And I should see "6" in the "#product_stockUnit_0_receivedQuantity" element
        And I should see "4" in the "#product_stockUnit_0_soldQuantity" element
        And I should see "0" in the "#product_stockUnit_0_shippedQuantity" element

        # Product #2 assertions
        When I go to "acme_product_product_admin_show" route with "productId:2"
        Then I should see "2" in the "#product_inStock" element
        And I should see "En rupture" in the "#product_stockState" element
        And I should see "Prête" in the "#product_stockUnit_0_state" element
        And I should see "2" in the "#product_stockUnit_0_orderedQuantity" element
        And I should see "2" in the "#product_stockUnit_0_receivedQuantity" element
        And I should see "2" in the "#product_stockUnit_0_soldQuantity" element
        And I should see "0" in the "#product_stockUnit_0_shippedQuantity" element

# TODO
#   - expected / available calculation (form)
