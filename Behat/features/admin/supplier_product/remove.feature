@commerce @supply @supplier-product
Feature: Remove supplier products
    In order to manage products supply
    As an administrator
    I need to be able to remove supplier products

    Background:
        Given I am logged in as an administrator
        And The following supplier carriers:
            | name |
            | TNT  |
        And The following suppliers:
            | name     | currency | carrier | email                | gender | lastName | firstName |
            | TechData | EUR      | TNT     | contact@techdata.com | mr     | Dupont   | Jean      |
        And The following supplier products:
            | supplier | designation          | reference | price     | weight | available | ordered | eda  |
            | TechData | Samsung Galaxy Tab A | S-GTAB-A  | 249.16667 | 0.5    | 40        | 0       |      |

    @javascript
    Scenario: Remove the supplier product
        When I go to "ekyna_commerce_supplier_product_admin_remove" route with "supplierId:1,supplierProductId:1"
        And I check "form[confirm]"
        And I press "form[actions][remove]"
        Then I should see the resource removed confirmation message
        And I should see "TechData"
        #And I show the "supplier-catalog" tab
        And I should not see "Samsung Galaxy Tab A"

    # TODO Scenario: Remove a supplier product used by a supplier order (should be prevented)
