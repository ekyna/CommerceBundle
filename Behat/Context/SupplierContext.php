<?php

namespace Ekyna\Bundle\CommerceBundle\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;

/**
 * Class SupplierContext
 * @package Ekyna\Bundle\CommerceBundle\Behat\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierContext implements Context, KernelAwareContext
{
    use KernelDictionary;

    /**
     * @Given The following suppliers:
     *
     * @param TableNode $table
     */
    public function createSuppliers(TableNode $table)
    {
        $suppliers = $this->castSuppliersTable($table);

        $manager = $this->getContainer()->get('ekyna_commerce.supplier.manager');

        foreach ($suppliers as $supplier) {
            $manager->persist($supplier);
        }

        $manager->flush();
        $manager->clear();
    }

    /**
     * @param TableNode $table
     *
     * @return array
     */
    private function castSuppliersTable(TableNode $table)
    {
        $currencyRepository = $this->getContainer()->get('ekyna_commerce.currency.repository');
        $supplierRepository = $this->getContainer()->get('ekyna_commerce.supplier.repository');

        $suppliers = [];
        foreach ($table->getHash() as $hash) {
            if (null === $currency = $currencyRepository->findOneByCode($hash['currency'])) {
                throw new \InvalidArgumentException("Failed to find the currency with code '{$hash['currency']}'.");
            }

            /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierInterface $supplier */
            $supplier = $supplierRepository->createNew();
            $supplier
                ->setName($hash['name'])
                ->setCurrency($currency)
                ->setEmail($hash['email'])
                ->setGender($hash['gender'])
                ->setLastName($hash['lastName'])
                ->setFirstName($hash['firstName']);

            $suppliers[] = $supplier;
        }

        return $suppliers;
    }
}
