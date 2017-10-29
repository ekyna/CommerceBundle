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
        $carrierRepository = $this->getContainer()->get('ekyna_commerce.supplier_carrier.repository');
        $supplierRepository = $this->getContainer()->get('ekyna_commerce.supplier.repository');

        $suppliers = [];
        foreach ($table as $row) {
            if (null === $currency = $currencyRepository->findOneByCode($row['currency'])) {
                throw new \InvalidArgumentException("Failed to find the currency with code '{$row['currency']}'.");
            }
            /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierCarrierInterface $carrier */
            if (null === $carrier = $carrierRepository->findOneBy(['name' => $row['carrier']])) {
                throw new \InvalidArgumentException("Failed to find the supplier carrier with name '{$row['carrier']}'.");
            }

            /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierInterface $supplier */
            $supplier = $supplierRepository->createNew();
            $supplier
                ->setName($row['name'])
                ->setCarrier($carrier)
                ->setCurrency($currency)
                ->setEmail($row['email'])
                ->setGender($row['gender'])
                ->setLastName($row['lastName'])
                ->setFirstName($row['firstName']);

            $suppliers[] = $supplier;
        }

        return $suppliers;
    }
}
