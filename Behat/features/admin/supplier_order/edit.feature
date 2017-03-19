@commerce @stock @supplier-order
Feature: Edit supplier orders
    In order to manage products supply
    As an administrator
    I need to be able to edit supplier orders

    Background:
        Given I am logged in as an administrator
        And The following suppliers:
            | name     | currency | email                | gender | lastName | firstName |
            | TechData | EUR      | contact@techdata.com | mr     | Dupont   | Jean      |
        And The following supplier orders:
            | number | supplier | currency | paymentTotal |
            | SO-001 | TechData | EUR      | 249.16667    |

    Scenario: Changing the supplier order estimated date of arrival
        When I go to "ekyna_commerce_supplier_order_admin_edit" route with "supplierOrderId:1"
        And I fill in "supplier_order[estimatedDateOfArrival]" with "01/01/2020"
        And I press "supplier_order_actions_save"
        Then I should see the resource saved confirmation message
        And I should see "TechData" in the "#order_supplier" element
        And I should see "249,17 €" in the "#order_paymentTotal" element
        And I should see "01/01/2020" in the "#order_estimatedDateOfArrival" element
        And I should see "Soumettre au fournisseur"
        And I show the "deliveries" tab
        And I should not see "Nouvelle livraison"
        And I should see "Aucune livraison fournisseur configuré"

#    Scenario: Setting ordered quantity as lower than the delivered quantity
#        Given The following supplier order items:
#            | order  | reference | quantity |
#            | SO-001 | IPAD-AIR  | 10       |
#        And The supplier order with number "SO-001" is submitted
#        And The following deliveries:
#            # TODO
#        And The following delivery items:
#            # TODO
#        When I go to "ekyna_commerce_supplier_order_admin_edit" route with "supplierOrderId:1"
#        And I fill in "supplier_order[compose][items][0][quantity]" with "9"
#        And I press "supplier_order_actions_save"
#        And I should see "La quantité commandée doit être supérieure ou égale à la quantité livrée"
#
#    Scenario: Removing a delivered item


    # TODO
    # - change items and test total
    # - Editing submitted orders should be prevented
    # - Removing an item
    # - stock units impact
