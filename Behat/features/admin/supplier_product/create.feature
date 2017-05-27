@commerce @supplier-product
Feature: Create supplier products
    In order to manage products supply
    As an administrator
    I need to be able to create new supplier products

    Background:
        Given I am logged in as an administrator
        And The following acme products:
            | designation | reference | price | weight |
            | iPad Air    | IPAD-AIR  | 290   | 0.8    |
        And The following suppliers:
            | name     | currency | email                | gender | lastName | firstName |
            | TechData | EUR      | contact@techdata.com | mr     | Dupont   | Jean      |

    @javascript
    Scenario: Create a supplier product
        When I go to "ekyna_commerce_supplier_product_admin_new" route with "supplierId:1"
        And I fill in "supplier_product[designation]" with "Samsung Galaxy Tab A"
        And I fill in "supplier_product[reference]" with "S-GTAB-A"
        And I fill in "supplier_product[netPrice]" with "249.16667"
        And I fill in "supplier_product[availableStock]" with "40"
        And I fill in "supplier_product[weight]" with "0.5"
        And I press "supplier_product_actions_save"
        Then I should see the resource saved confirmation message
        And I should see "TechData"
        #And I show the "supplier-catalog" tab
        And I should see "Samsung Galaxy Tab A"

    @javascript @subject
    Scenario: Create a supplier product with subject
        When I go to "ekyna_commerce_supplier_product_admin_new" route with "supplierId:1"
        And I fill in "supplier_product[designation]" with "iPar Air"
        And I fill in "supplier_product[reference]" with "IPAD-AIR"
        And I fill in "supplier_product[netPrice]" with "200"
        And I fill in "supplier_product[availableStock]" with "40"
        And I fill in "supplier_product[weight]" with "0.8"
        And I select "Acme Product" from "supplier_product[subjectIdentity][provider]"
        And I search "iPad" in "supplier_product[subjectIdentity][subject]" and select the first result
        And I press "supplier_product_actions_save"
        Then I should see the resource saved confirmation message
        And I should see "TechData"
        #And I show the "supplier-catalog" tab
        And I should see "IPAD-AIR"
