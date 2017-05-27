@commerce @tax-rule
Feature: Create tax rules
    In order to manage sales
    As an administrator
    I need to be able to create new tax rules

    Background:
        Given I am logged in as an administrator

    Scenario: Create a tax rule
        When I go to "ekyna_commerce_tax_rule_admin_new" route
        And I fill in "tax_rule[name]" with "Test tax rule"
        And I fill in "tax_rule[priority]" with "10"
        And I check "tax_rule[business]"
        And I select "France" from "tax_rule[countries][]"
        And I select "TVA 20%" from "tax_rule[taxes][]"
        And I add element to collection field "tax_rule_notices"
        And I fill in "tax_rule[notices][0]" with "Test notice"
        And I press "tax_rule_actions_save"
        Then I should see the resource saved confirmation message
        And I should see "Test tax rule" in the "#tax_rule_name" element
        And I should see "10" in the "#tax_rule_priority" element
        And I should see "Non" in the "#tax_rule_customer" element
        And I should see "Oui" in the "#tax_rule_business" element
        And I should see "France" in the "#tax_rule_countries" element
        And I should see "TVA 20%" in the "#tax_rule_taxes" element
        And I should see "Test notice" in the "#tax_rule_notices" element

        # TODO
        # - priority
        # - can't create without checking customer or business
