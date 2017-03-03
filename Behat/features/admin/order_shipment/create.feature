@commerce @order-shipment
Feature: Create order shipments
    In order to deliver products
    As an administrator
    I need to be able to create new order shipments

    Background:
        Given I am logged in as an administrator
        And The following taxes:
            | name    | rate | country |
            | TVA 20% | 20   | fr      |
        And The following tax rules:
            | name       | taxes   |
            | TVA France | TVA 20% |
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
            | SO-001 | GALA-TAB  | 10       |
        And The supplier order with number "SO-001" is submitted

        And The following orders:
            | number | customer           | street            | postalCode | city   | shipmentMethod |
            | O-0001 | contact@dupont.com | 10 rue de la soif | 35000      | Rennes | GLS            |
        And The following order items:
            | order  | quantity | acme_product |
            | O-0001 | 4        | IPAD-AIR     |
            | O-0001 | 2        | GALA-TAB     |

    @javascript
    Scenario: Add an order shipment
        When I go to "ekyna_commerce_order_shipment_admin_new" route with "{orderId:1}"
        And I fill in "order_shipment[items][0][quantity]" with "4"
        And I fill in "order_shipment[items][1][quantity]" with "2"
        And I press "order_shipment[actions][save]"

        # Shipment assertions
        Then I show the "shipments" tab
        Then I should see "GLS" in the "#shipment_0_method" element
        Then I should see "Création" in the "#shipment_0_state" element

    @javascript
    Scenario: Add an order shipment
        When I go to "ekyna_commerce_order_shipment_admin_new" route with "{orderId:1}"
        And I select "Expédié" from "order_shipment[state]"
        And I fill in "order_shipment[items][0][quantity]" with "4"
        And I fill in "order_shipment[items][1][quantity]" with "2"
        And I press "order_shipment[actions][save]"

        # Shipment assertions
        Then I show the "shipments" tab
        Then I should see "GLS" in the "#shipment_0_method" element
        Then I should see "Expédié" in the "#shipment_0_state" element

        # Product assertions
#        When I go to "acme_product_product_admin_show" route with "productId:1"
#        Then I should see "2" in the "#product_orderedStock" element
#        Then I should see "Pré-commande" in the "#product_stockState" element

        # Stock units assertions
#        Then I should see "En attente" in the "#product_stockUnit_0_state" element
#        Then I should see "2" in the "#product_stockUnit_0_orderedQuantity" element

