<?php

namespace Ekyna\Bundle\CommerceBundle\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;

/**
 * Class SupplierOrderContext
 * @package Ekyna\Bundle\CommerceBundle\Behat\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderContext implements Context, KernelAwareContext
{
    use KernelDictionary;

    /**
     * @Given The following supplier orders:
     *
     * @param TableNode $table
     */
    public function createSupplierOrders(TableNode $table)
    {
        $supplierOrders = $this->castSupplierOrdersTable($table);

        $manager = $this->getContainer()->get('ekyna_commerce.supplier_order.manager');

        foreach ($supplierOrders as $supplierOrder) {
            $manager->persist($supplierOrder);
        }

        $manager->flush();
        $manager->clear();
    }

    /**
     * @param TableNode $table
     *
     * @return array
     */
    private function castSupplierOrdersTable(TableNode $table)
    {
        $supplierRepository = $this->getContainer()->get('ekyna_commerce.supplier.repository');
        $currencyRepository = $this->getContainer()->get('ekyna_commerce.currency.repository');
        $supplierOrderRepository = $this->getContainer()->get('ekyna_commerce.supplier_order.repository');

        $supplierOrders = [];
        foreach ($table->getHash() as $hash) {
            if (null === $supplier = $supplierRepository->findOneBy(['name' => $hash['supplier']])) {
                throw new \InvalidArgumentException("Failed to find the supplier named '{$hash['supplier']}'.");
            }
            if (null === $currency = $currencyRepository->findOneBy(['code' => $hash['currency']])) {
                throw new \InvalidArgumentException("Failed to find the currency for code '{$hash['currency']}'.");
            }

            /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface $supplierOrder */
            $supplierOrder = $supplierOrderRepository->createNew();
            $supplierOrder
                ->setSupplier($supplier)
                ->setCurrency($currency);

            $supplierOrders[] = $supplierOrder;
        }

        return $supplierOrders;
    }
}
