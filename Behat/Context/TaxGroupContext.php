<?php

namespace Ekyna\Bundle\CommerceBundle\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;

/**
 * Class TaxGroupContext
 * @package Ekyna\Bundle\CommerceBundle\Behat\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxGroupContext implements Context, KernelAwareContext
{
    use KernelDictionary;

    /**
     * @Given The following tax groups:
     *
     * @param TableNode $table
     */
    public function createTaxGroups(TableNode $table)
    {
        $taxGroups = $this->castTaxGroupsTable($table);

        $manager = $this->getContainer()->get('ekyna_commerce.tax_group.manager');

        foreach ($taxGroups as $taxGroup) {
            $manager->persist($taxGroup);
        }

        $manager->flush();
        $manager->clear();
    }

    /**
     * @param TableNode $table
     *
     * @return array|\Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface[]
     */
    private function castTaxGroupsTable(TableNode $table)
    {
        $taxGroupRepository = $this->getContainer()->get('ekyna_commerce.tax_group.repository');
        $taxRuleRepository = $this->getContainer()->get('ekyna_commerce.tax_rule.repository');

        $taxGroups = [];
        foreach ($table as $row) {
            /** @var \Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface $taxGroup */
            $taxGroup = $taxGroupRepository->createNew();

            // Tax rules
            if (isset($row['taxRules'])) {
                foreach (explode(',', $row['taxRules']) as $name) {
                    if (null === $taxRule = $taxRuleRepository->findOneBy(['name' => $name])) {
                        throw new \InvalidArgumentException("Failed to find the tax rule with name '{$name}'.");
                    }
                    $taxGroup->addTaxRule($taxRule);
                }
            } else {
                $taxGroup->addTaxRule($taxRuleRepository->findDefault());
            }

            $taxGroup->setName($row['name']);

            if (isset($row['default'])) {
                $taxGroup->setDefault($row['default']);
            }

            $taxGroups[] = $taxGroup;
        }

        return $taxGroups;
    }
}
