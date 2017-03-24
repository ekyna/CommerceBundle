@commerce @payment-term
Feature: Create payment terms
    In order to sell products
    As an administrator
    I need to be able to create new payment terms

    Background:
        Given I am logged in as an administrator

    @javascript
    Scenario: Create a payment term
        When I go to "ekyna_commerce_payment_term_admin_new" route
        And I fill in "payment_term[name]" with "30 jours fin de mois"
        And I fill in "payment_term[days]" with "30"
        And I check "payment_term[endOfMonth]"
        And I fill in "payment_term[translations][fr][title]" with "30 jours fin de mois"
        And I press "payment_term_actions_save"

        Then I should see the resource saved confirmation message
        And I should see "30 jours fin de mois" in the "#payment_term_name" element
        And I should see "30" in the "#payment_term_days" element
        And I should see "Oui" in the "#payment_term_endOfMonth" element
        And I should see "30 jours fin de mois" in the "#payment_term_translations_fr_title" element
