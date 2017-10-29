@commerce @sale @order-item
Feature: Create order items
    In order to sell products
    As an administrator
    I need to be able to create new orders

    Background:
        Given I am logged in as an administrator
        And The following acme products:
            | designation | reference | price | weight |
            | iPad Air    | IPAD-AIR  | 290   | 0.8    |
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
        And The following orders:
            | customer           | street            | postalCode | city   |
            | contact@dupont.com | 10 rue de la soif | 35000      | Rennes |

    @javascript
    Scenario: Add an order item
        When I go to "ekyna_commerce_order_admin_show" route with "{orderId:1}"
        And I show the "details" tab
        And I click "order_item_add"
        And I wait for the modal to appear
        And I wait for the form "ekyna_commerce_sale_item_subject" to appear
        And I select "Acme Product" from "ekyna_commerce_sale_item_subject[subjectIdentity][provider]"
        And I search "iPad" in "ekyna_commerce_sale_item_subject[subjectIdentity][subject]" and select the first result
        And I press "form_flow_submit"
        And I wait for the form "sale_item_configure" to appear
        And I fill in "sale_item_configure[quantity]" with "2"
        And I press "form_flow_submit"
        And I wait for the modal to disappear

        # Items assertions
        Then I should see "iPad Air" in the "#item_0_designation" element
        And I should see "IPAD-AIR" in the "#item_0_reference" element
        And I should see "290,00" in the "#item_0_unit" element
        And I should see "20%" in the "#item_0_taxes" element
        # TODO And I should see "2" in the "#item_0_quantity" element
        And I should see "580,00" in the "#item_0_base" element

        # Shipment assertions
        And I should see "1,600" in the "#shipment_designation" element
        And I should see "0,00" in the "#shipment_base" element

        # Final assertions
        And I should see "580,00" in the "#final_base" element
        And I should see "116,00" in the "#final_tax" element
        And I should see "696,00" in the "#final_total" element


        #And I should see "TVA France - marchandises et services" in the "#tax_rule_name" element
        #And I should see "Taux normal" in the "#tax_rule_taxGroups" element
        #And I should see "Particuliers" in the "#tax_rule_customerGroups" element
        #And I should see "TVA 20%" in the "#tax_rule_taxes" element
