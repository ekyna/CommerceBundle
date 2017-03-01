<?php

namespace Ekyna\Bundle\CommerceBundle\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;

/**
 * Class TaxContext
 * @package Ekyna\Bundle\CommerceBundle\Behat\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxContext implements Context, KernelAwareContext
{
    use KernelDictionary;

    /**
     * @Given The following taxes:
     *
     * @param TableNode $table
     */
    public function createTaxes(TableNode $table)
    {
        $taxes = $this->castTaxesTable($table);

        $manager = $this->getContainer()->get('ekyna_commerce.tax.manager');

        foreach ($taxes as $tax) {
            $manager->persist($tax);
        }

        $manager->flush();
        $manager->clear();
    }

    /**
     * @param TableNode $table
     *
     * @return array
     */
    private function castTaxesTable(TableNode $table)
    {
        $countryRepository = $this->getContainer()->get('ekyna_commerce.country.repository');
        $taxRepository = $this->getContainer()->get('ekyna_commerce.tax.repository');

        $taxes = [];
        foreach ($table as $row) {
            if (null === $country = $countryRepository->findOneByCode($row['country'])) {
                throw new \InvalidArgumentException("Failed to find the country with code '{$row['country']}'.");
            }

            /** @var \Ekyna\Component\Commerce\Pricing\Model\TaxInterface $tax */
            $tax = $taxRepository->createNew();
            $tax
                ->setName($row['name'])
                ->setRate($row['rate'])
                ->setCountry($country);

            $taxes[] = $tax;
        }

        return $taxes;
    }
}
