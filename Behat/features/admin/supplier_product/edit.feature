@commerce
Feature: Edit supplier products
    In order to manage products supply
    As an administrator
    I need to be able to edit supplier products

    Background:
        Given I am logged in as an administrator
        And The following suppliers:
            | name     | currency | email                | gender | lastName | firstName |
            | TechData | EUR      | contact@techdata.com | mr     | Dupont   | Jean      |
        And The following supplier products:
            | supplier | designation          | reference | price     | weight | available | ordered | eda  |
            | TechData | Samsung Galaxy Tab A | S-GTAB-A  | 249.16667 | 0.5    | 40        | 0       |      |

    @supplier-product
    Scenario: Edit the supplier product
        When I go to "ekyna_commerce_supplier_product_admin_edit" route with "supplierId:1,supplierProductId:1"
        And I fill in "supplier_product[designation]" with "Samsung Galaxy Tab B"
        And I fill in "supplier_product[reference]" with "S-GTAB-B"
        And I press "supplier_product_actions_save"
        Then I should see the resource saved confirmation message
        And I should see "TechData"
        And I should see "Samsung Galaxy Tab B"
        And I should see "S-GTAB-B"
