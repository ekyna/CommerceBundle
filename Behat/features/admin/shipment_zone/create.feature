@commerce @shipment-zone
Feature: Create shipment zones
    In order to sell products
    As an administrator
    I need to be able to create new shipment zones

    Background:
        Given I am logged in as an administrator

    @javascript
    Scenario: Create a shipment zone
        When I go to "ekyna_commerce_shipment_zone_admin_new" route
        And I fill in "shipment_zone[name]" with "France métropolitaine"
        And I select "France" from "shipment_zone[countries][]"
        And I press "shipment_zone_actions_save"
        Then I should see the resource saved confirmation message
        And I should see "France métropolitaine" in the "#shipment_zone_name" element
        And I should see "France" in the "#shipment_zone_countries" element
