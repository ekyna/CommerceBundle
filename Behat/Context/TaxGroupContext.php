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
        $taxRepository = $this->getContainer()->get('ekyna_commerce.tax.repository');

        $taxGroups = [];
        foreach ($table as $row) {
            /** @var \Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface $taxGroup */
            $taxGroup = $taxGroupRepository->createNew();

            // Taxes
            foreach (explode(',', $row['taxes']) as $name) {
                /** @var \Ekyna\Component\Commerce\Pricing\Model\TaxInterface $tax */
                if (null === $tax = $taxRepository->findOneBy(['name' => $name])) {
                    throw new \InvalidArgumentException("Failed to find the tax with name '{$name}'.");
                }
                $taxGroup->addTax($tax);
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
