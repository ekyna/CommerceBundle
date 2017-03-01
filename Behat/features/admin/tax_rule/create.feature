@commerce @tax-rule
Feature: Create tax rules
    In order to manage sales
    As an administrator
    I need to be able to create new tax rules

    Background:
        Given I am logged in as an administrator
        And The following taxes:
            | name    | rate | country |
            | TVA 20% | 20   | FR      |

    Scenario: Create a tax rule
        When I go to "ekyna_commerce_tax_rule_admin_new" route
        And I fill in "tax_rule[name]" with "TVA France - marchandises et services"
        And I select "Default tax group" from "tax_rule[taxGroups][]"
        And I select "Default customer group" from "tax_rule[customerGroups][]"
        And I select "TVA 20%" from "tax_rule[taxes][]"
        And I press "tax_rule_actions_save"
        Then I should see the resource saved confirmation message
        And I should see "TVA France - marchandises et services" in the "#tax_rule_name" element
        And I should see "Default tax group" in the "#tax_rule_taxGroups" element
        And I should see "Default customer group" in the "#tax_rule_customerGroups" element
        And I should see "TVA 20%" in the "#tax_rule_taxes" element

        # TODO
        # - priority
