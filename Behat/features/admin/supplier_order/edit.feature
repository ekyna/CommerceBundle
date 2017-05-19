@commerce @stock @supplier-order
Feature: Edit supplier orders
    In order to manage products supply
    As an administrator
    I need to be able to edit supplier orders

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
            | number | supplier | currency | shippingCost |
            | SO-001 | TechData | EUR      | 30           |
        And The following supplier order items:
            | order  | reference | quantity |
            | SO-001 | IPAD-AIR  | 10       |

    Scenario: Changing the supplier order estimated date of arrival
        When I go to "ekyna_commerce_supplier_order_admin_edit" route with "supplierOrderId:1"
        And I fill in "supplier_order[estimatedDateOfArrival]" with "01/01/2020"
        And I press "supplier_order_actions_save"
        Then I should see the resource saved confirmation message
        And I should see "Création" in the "#order_state" element
        And I should see "TechData" in the "#order_supplier" element
        And I should see "30,00 €" in the "#order_shippingCost" element
        And I should see "2 521,70 €" in the "#order_paymentTotal" element
        And I should see "01/01/2020" in the "#order_estimatedDateOfArrival" element
        And I should see "Soumettre au fournisseur"
        And I show the "deliveries" tab
        And I should not see "Nouvelle livraison"
        And I should see "Aucune livraison fournisseur configuré"

    Scenario: Setting ordered quantity as lower than the received quantity
        Given The supplier order with number "SO-001" is submitted
        And The following supplier deliveries:
            | order |
            | SO-001 |
        And The following supplier delivery items:
            | order  | delivery | reference | quantity |
            | SO-001 | 0        | IPAD-AIR  | 10       |
        When I go to "ekyna_commerce_supplier_order_admin_edit" route with "supplierOrderId:1"
        And I fill in "supplier_order[compose][items][0][quantity]" with "9"
        And I press "supplier_order_actions_save"
        Then I should see "La quantité commandée doit être supérieure ou égale à la quantité réceptionnée"

    Scenario: Removing a received item
        Given The supplier order with number "SO-001" is submitted
        And The following supplier deliveries:
            | order |
            | SO-001 |
        And The following supplier delivery items:
            | order  | delivery | reference | quantity |
            | SO-001 | 0        | IPAD-AIR  | 10       |
        When I go to "ekyna_commerce_supplier_order_admin_edit" route with "supplierOrderId:1"
        And I remove element with index "0" from collection field "supplier_order_compose_items"
        And I press "supplier_order_actions_save"
        Then I should see "Au moins une ligne de la commande ne correspond pas aux lignes des livraisons"


    # TODO
    # - change items and test total
    # - Editing submitted orders should be prevented
    # - Removing an item
    # - stock units impact
