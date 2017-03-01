<?php

namespace Ekyna\Bundle\CommerceBundle\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;

/**
 * Class TaxRuleContext
 * @package Ekyna\Bundle\CommerceBundle\Behat\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxRuleContext implements Context, KernelAwareContext
{
    use KernelDictionary;

    /**
     * @Given The following tax rules:
     *
     * @param TableNode $table
     */
    public function createTaxRules(TableNode $table)
    {
        $taxRules = $this->castTaxRulesTable($table);

        $manager = $this->getContainer()->get('ekyna_commerce.tax_rule.manager');

        foreach ($taxRules as $taxRule) {
            $manager->persist($taxRule);
        }

        $manager->flush();
        $manager->clear();
    }

    /**
     * @param TableNode $table
     *
     * @return array|\Ekyna\Component\Commerce\Pricing\Model\TaxRuleInterface[]
     */
    private function castTaxRulesTable(TableNode $table)
    {
        $taxRuleRepository = $this->getContainer()->get('ekyna_commerce.tax_rule.repository');
        $taxGroupRepository = $this->getContainer()->get('ekyna_commerce.tax_group.repository');
        $customerGroupRepository = $this->getContainer()->get('ekyna_commerce.customer_group.repository');
        $taxRepository = $this->getContainer()->get('ekyna_commerce.tax.repository');

        $taxRules = [];
        foreach ($table as $row) {
            /** @var \Ekyna\Component\Commerce\Pricing\Model\TaxRuleInterface $taxRule */
            $taxRule = $taxRuleRepository->createNew();

            // Tax groups
            if (isset($row['taxGroups'])) {
                foreach (explode(',', $row['taxGroups']) as $name) {
                    if (null === $taxGroup = $taxGroupRepository->findOneBy(['name' => $name])) {
                        throw new \InvalidArgumentException("Failed to find the tax group with name '{$name}'.");
                    }
                    $taxRule->addTaxGroup($taxGroup);
                }
            } else {
                $taxRule->addTaxGroup($taxGroupRepository->findDefault());
            }

            // Customer groups
            if (isset($row['customerGroups'])) {
                foreach (explode(',', $row['customerGroups']) as $name) {
                    if (null === $customerGroup = $customerGroupRepository->findOneBy(['name' => $name])) {
                        throw new \InvalidArgumentException("Failed to find the customer group with name '{$name}'.");
                    }
                    $taxRule->addCustomerGroup($customerGroup);
                }
            } else {
                $taxRule->addCustomerGroup($customerGroupRepository->findDefault());
            }

            // Taxes
            foreach (explode(',', $row['taxes']) as $name) {
                if (null === $tax = $taxRepository->findOneBy(['name' => $name])) {
                    throw new \InvalidArgumentException("Failed to find the tax with name '{$name}'.");
                }
                $taxRule->addTax($tax);
            }

            $taxRule->setName($row['name']);

            if (isset($row['priority'])) {
                $taxRule->setPriority($row['priority']);
            }

            $taxRules[] = $taxRule;
        }

        return $taxRules;
    }
}
