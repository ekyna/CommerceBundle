@commerce
Feature: Create supplier deliveries
    In order to manage products supply
    As an administrator
    I need to be able to submit supplier deliveries

    Background:
        Given I am logged in as an administrator
        And The following acme products:
            | designation | reference | price | weight |
            | iPad Air    | IPAD-AIR  | 290   | 0.8    |
        And The following suppliers:
            | name     | currency | email                | gender | lastName | firstName |
            | TechData | EUR      | contact@techdata.com | mr     | Dupont   | Jean      |
        And The following supplier products:
            | supplier | designation | reference | price     | weight | available | ordered | eda  | provider     | identifier |
            | TechData | iPad Air    | IPAD-AIR  | 249.16667 | 0.8    | 40        | 0       |      | acme_product | 1          |
        And The following supplier orders:
            | number | supplier | currency | paymentTotal | state   | estimatedDateOfArrival |
            | SO-001 | TechData | EUR      | 249.16667    | ordered | 01/01/2020             |
        And The supplier order with number "SO-001" has the following items:
            | reference | quantity |
            | IPAD-AIR  | 2        |

    @supplier-delivery @current
    Scenario: Create the supplier delivery
        When I go to "ekyna_commerce_supplier_delivery_admin_new" route with "supplierOrderId:1"
        And I fill in "supplier_delivery[items][0][quantity]" with "2"
        And I press "supplier_delivery[actions][save]"
        Then I should see the resource saved confirmation message

        # Order assertions
        And I should see "TechData" in the "#order_supplier" element
        And I should see "249,17" in the "#order_paymentTotal" element
        And I should see "Completed" in the "#order_state" element
        # TODO And I should see "01/01/2020" in the "#order_estimatedDateOfArrival" element
        # TODO And I should not see "Soumettre au fournisseur"

        # Items assertions
        And I should see "iPad Air" in the "#item_0_designation" element
        And I should see "IPAD-AIR" in the "#item_0_reference" element
        And I should see "2" in the "#item_0_quantity" element
        And I should see "249,17" in the "#item_0_netPrice" element
        And I should see "iPad Air" in the "#item_0_subject" element

        # Product assertions
        When I go to "acme_product_product_admin_show" route with "productId:1"
        # TODO Quantity is no longer ordered at subject(product) level
        Then I should see "0" in the "#product_orderedStock" element
        Then I should see "2" in the "#product_inStock" element

        # Stock units assertions
        Then I should see "Ouverte" in the "#product_stockUnit_0_state" element
        Then I should see "2" in the "#product_stockUnit_0_orderedQuantity" element
        Then I should see "2" in the "#product_stockUnit_0_deliveredQuantity" element
