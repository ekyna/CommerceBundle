@commerce @order
Feature: Create orders
    In order to sell products
    As an administrator
    I need to be able to create new orders

    Background:
        Given I am logged in as an administrator
        And The following customers:
            | email              | company        | gender | lastName | firstName |
            | contact@dupont.com | Dupont et fils | mr     | Dupont   | Jean      |
        And The customer "contact@dupont.com" has the following addresses:
            | company        | gender | lastName | firstName | street            | postalCode | city   | country |
            | Dupont et fils | mr     | Dupont   | Jean      | 10 rue de la soif | 35000      | Rennes | FR      |

    @javascript
    Scenario: Create an order without customer
        When I go to "ekyna_commerce_order_admin_new" route
        And I select "Default customer group" from "order[customerGroup]"
        And I fill in "order[company]" with "Dupont et fils"
        And I select "Mr" from "order[identity][gender]"
        And I fill in "order[identity][lastName]" with "Dupont"
        And I fill in "order[identity][firstName]" with "Jean"
        And I fill in "order[email]" with "contact@dupont.com"
        And I show the "addresses" tab
        And I fill in "order[invoiceAddress][address][company]" with "Dupont et fils"
        And I select "Mr" from "order[invoiceAddress][address][identity][gender]"
        And I fill in "order[invoiceAddress][address][identity][lastName]" with "Dupont"
        And I fill in "order[invoiceAddress][address][identity][firstName]" with "Jean"
        And I fill in "order[invoiceAddress][address][street]" with "10 rue de la soif"
        And I fill in "order[invoiceAddress][address][postalCode]" with "35000"
        And I fill in "order[invoiceAddress][address][city]" with "Rennes"
        And I select "France" from "order[invoiceAddress][address][country]"
        And I fill in "order[invoiceAddress][address][phone]" with "0298765432"
        And I fill in "order[invoiceAddress][address][mobile]" with "0612345678"
        And I check "order[deliveryAddress][sameAddress]"
        And I press "order_actions_save"

        Then I should see the resource saved confirmation message
        # Order assertions
        And I should see "Indéfini" in the "#sale_customer" element
        And I should see "Dupont et fils" in the "#sale_company" element
        And I should see "Default customer group" in the "#sale_customerGroup" element
        And I should see "Mr Jean Dupont" in the "#sale_identity" element
        And I should see "contact@dupont.com" in the "#sale_email" element

        And I should see "0,00 €" in the "#sale_grandTotal" element
        And I should see "Création" in the "#sale_state" element
        And I should see "0,00 €" in the "#sale_paidTotal" element
        And I should see "Création" in the "#sale_paymentState" element
        And I should see "0 kg" in the "#order_weightTotal" element
        And I should see "Aucun" in the "#order_shipmentState" element

        And I should see "Mr Jean Dupont" in the "#sale_invoiceAddress" element
        And I should see "10 rue de la soif" in the "#sale_invoiceAddress" element
        And I should see "35000 Rennes" in the "#sale_invoiceAddress" element
        And I should see "France" in the "#sale_invoiceAddress" element
        And I should see "+33 2 98 76 54 32" in the "#sale_invoiceAddress" element
        And I should see "+33 6 12 34 56 78" in the "#sale_invoiceAddress" element

        And I should see "Même adresse" in the "#sale_deliveryAddress" element

    @javascript
    Scenario: Create an order with customer
        When I go to "ekyna_commerce_order_admin_new" route
        And I search "dupont" in "order[customer]" and select the first result
        And I show the "addresses" tab
        And I wait for Select2 initialization on "order[invoiceAddress][choice]"
        And I wait for "order[invoiceAddress][choice]" to be enabled
        And I select "10 rue de la soif 35000 Rennes" from "order[invoiceAddress][choice]"
        And I check "order[deliveryAddress][sameAddress]"
        And I press "order_actions_save"

        Then I should see the resource saved confirmation message
        # Order assertions
        And I should see "Mr Jean Dupont" in the "#sale_customer" element
        And I should see "Dupont et fils" in the "#sale_company" element
        And I should see "Default customer group" in the "#sale_customerGroup" element
        And I should see "Mr Jean Dupont" in the "#sale_identity" element
        And I should see "contact@dupont.com" in the "#sale_email" element

        And I should see "0,00 €" in the "#sale_grandTotal" element
        And I should see "Création" in the "#sale_state" element
        And I should see "0,00 €" in the "#sale_paidTotal" element
        And I should see "Création" in the "#sale_paymentState" element
        And I should see "0 kg" in the "#order_weightTotal" element
        And I should see "Aucun" in the "#order_shipmentState" element

        And I should see "Mr Jean Dupont" in the "#sale_invoiceAddress" element
        And I should see "10 rue de la soif" in the "#sale_invoiceAddress" element
        And I should see "35000 Rennes" in the "#sale_invoiceAddress" element
        And I should see "France" in the "#sale_invoiceAddress" element
#        And I should see "+33 2 98 76 54 32" in the "#sale_invoiceAddress" element
#        And I should see "+33 6 12 34 56 78" in the "#sale_invoiceAddress" element

        And I should see "Même adresse" in the "#sale_deliveryAddress" element

    # TODO
    # - set items
    # - set items from order products
    # - test total
