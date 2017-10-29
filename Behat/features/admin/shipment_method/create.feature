@commerce @shipment-method
Feature: Create shipment methods
    In order to sell products
    As an administrator
    I need to be able to create new shipment methods

    Background:
        Given I am logged in as an administrator
        And The following shipment zones:
            | name   | countries |
            | France | FR        |

    @javascript
    Scenario: Create a shipment method with price grid
        When I go to "ekyna_commerce_shipment_method_admin_new" route

        # Platform choice
        And I wait for Select2 initialization on "shipment_method_factory_choice[platformName]"
        And I select "Noop" from "shipment_method_factory_choice[platformName]"
        And I press "form_flow_submit"

        # Configuration
        And I wait for Select2 initialization on "shipment_method[taxGroup]"
        And I fill in "shipment_method[name]" with "GLS"
        And I select "Taux normal" from "shipment_method[taxGroup]"
        And I check "shipment_method[available]"
        And I check "shipment_method[enabled]"

        And I show the "content" tab
        And I fill in "shipment_method[translations][fr][title]" with "Titre GLS"
        And I fill in tinymce "shipment_method[translations][fr][description]" with "Description GLS"

        And I show the "pricing" tab
        And I select "France" from "shipment_method[pricing][filter]"
        And I wait "1" seconds
        And I press "Ajouter"
        And I fill in "shipment_method[pricing][prices][0][weight]" with "2"
        And I fill in "shipment_method[pricing][prices][0][netPrice]" with "8.5"
        And I press "Ajouter"
        And I fill in "shipment_method[pricing][prices][1][weight]" with "5"
        And I fill in "shipment_method[pricing][prices][1][netPrice]" with "12.5"
        And I press "Ajouter"
        And I fill in "shipment_method[pricing][prices][2][weight]" with "10"
        And I fill in "shipment_method[pricing][prices][2][netPrice]" with "18.5"
        And I press "form_flow_submit"

        # Assertions
        Then I should see the resource saved confirmation message

        And I should see "GLS" in the "#shipment_method_name" element
        And I should see "Taux normal" in the "#shipment_method_taxGroup" element
        And I should see "Oui" in the "#shipment_method_available" element
        And I should see "Oui" in the "#shipment_method_enabled" element

        And I show the "content" tab
        And I should see "Titre GLS" in the "#shipmentMethod_translations_fr_title" element
        And I should see "Description GLS" in the "#shipmentMethod_translations_fr_description" element

        And I show the "pricing" tab
        And I select "France" from "shipment-price-filter"
        And I should see "2 kg" in the "#shipment_price_0_weight" element
        And I should see "8,50 €" in the "#shipment_price_0_netPrice" element
        And I should see "5 kg" in the "#shipment_price_1_weight" element
        And I should see "12,50 €" in the "#shipment_price_1_netPrice" element
        And I should see "10 kg" in the "#shipment_price_2_weight" element
        And I should see "18,50 €" in the "#shipment_price_2_netPrice" element
