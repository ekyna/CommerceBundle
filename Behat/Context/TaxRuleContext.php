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
        $countryRepository = $this->getContainer()->get('ekyna_commerce.country.repository');
        $taxRepository = $this->getContainer()->get('ekyna_commerce.tax.repository');

        $taxRules = [];
        foreach ($table as $row) {
            /** @var \Ekyna\Component\Commerce\Pricing\Model\TaxRuleInterface $taxRule */
            $taxRule = $taxRuleRepository->createNew();

            // Countries
            if (isset($row['countries'])) {
                foreach (explode(',', $row['countries']) as $code) {
                    if (null === $country = $countryRepository->findOneByCode($code)) {
                        throw new \InvalidArgumentException("Failed to find the country with code '{$code}'.");
                    }
                    $taxRule->addCountry($country);
                }
            } else {
                $taxRule->addCountry($countryRepository->findDefault());
            }

            // Taxes
            foreach (explode(',', $row['taxes']) as $name) {
                /** @var \Ekyna\Component\Commerce\Pricing\Model\TaxInterface $tax */
                if (null === $tax = $taxRepository->findOneBy(['name' => $name])) {
                    throw new \InvalidArgumentException("Failed to find the tax with name '{$name}'.");
                }
                $taxRule->addTax($tax);
            }

            $taxRule->setName($row['name']);

            if (isset($row['priority'])) {
                $taxRule->setPriority($row['priority']);
            }
            if (isset($row['customer'])) {
                $taxRule->setCustomer($row['customer']);
            }
            if (isset($row['business'])) {
                $taxRule->setPriority($row['business']);
            }

            $taxRules[] = $taxRule;
        }

        return $taxRules;
    }
}
