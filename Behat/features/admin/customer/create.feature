@commerce @customer
Feature: Create customers
    In order to create orders
    As an administrator
    I need to be able to create new customers

    Background:
        Given I am logged in as an administrator

    Scenario: Create a customer
        When I go to "ekyna_commerce_customer_admin_new" route
        And I wait for Select2 initialization on "customer[paymentMethods][]"
        And I wait for phone number initialization on "customer[phone]"
        And I wait for phone number initialization on "customer[mobile]"
        And I fill in "customer[email]" with "contact@dupont.com"
        And I fill in "customer[company]" with "Dupont et fils"
        And I select "Mr" from "customer[identity][gender]"
        And I fill in "customer[identity][lastName]" with "Dupont"
        And I fill in "customer[identity][firstName]" with "Jean"
#        And I fill in "customer[phone][country]" with "FR"
#        And I fill in "customer[phone]" with "0298765432"
#        And I fill in "customer[mobile][country]" with "FR"
#        And I fill in "customer[mobile]" with "0612345678"
        And I press "customer_actions_save"

        #Then I should see the resource saved confirmation message
        Then I should see "contact@dupont.com" in the "#customer_email" element
        And I should see "Dupont et fils" in the "#customer_company" element
        And I should see "Mr Dupont Jean" in the "#customer_identity" element
#        And I should see "+33 2 98 76 54 32" in the "#customer_phone" element
#        And I should see "+33 6 12 34 56 78" in the "#customer_mobile" element

    # TODO
    # - Different group
    # - With parent
    # - With user
    # - Unique constraint
