@commerce @customer-address
Feature: Create customer addresses
    In order to create orders
    As an administrator
    I need to be able to create new customers

    Background:
        Given I am logged in as an administrator
        And The following customers:
            | email              | company        | gender | lastName | firstName |
            | contact@dupont.com | Dupont et fils | mr     | Dupont   | Jean      |

    @javascript
    Scenario: Create a customer address
        When I go to "ekyna_commerce_customer_address_admin_new" route with "{customerId:1}"
        And I fill in "customer_address[company]" with "Dupont et fils"
        And I select "Mr" from "customer_address[identity][gender]"
        And I fill in "customer_address[identity][lastName]" with "Dupont"
        And I fill in "customer_address[identity][firstName]" with "Jean"
        And I fill in "customer_address[street]" with "10 rue de la paix"
        And I fill in "customer_address[postalCode]" with "12345"
        And I fill in "customer_address[city]" with "Paris"
        And I select "France" from "customer_address[country]"
        And I fill in "customer_address[phone]" with "0298765432"
        And I fill in "customer_address[mobile]" with "0612345678"

        And I press "customer_address_actions_save"
        Then I should see the resource saved confirmation message

        # Customer assertion
        And I should see "contact@dupont.com" in the "#customer_email" element

        # Address assertion
        And I show the "addresses" tab
        And I should see "Dupont et fils" in the "#customer_address_0" element
        And I should see "Mr Jean Dupont" in the "#customer_address_0" element
        And I should see "10 rue de la paix" in the "#customer_address_0" element
        And I should see "12345 Paris" in the "#customer_address_0" element
        And I should see "France" in the "#customer_address_0" element
        And I should see "+33 2 98 76 54 32" in the "#customer_address_0" element
        And I should see "+33 6 12 34 56 78" in the "#customer_address_0" element

    # TODO
    # - Default for invoice/delivery
