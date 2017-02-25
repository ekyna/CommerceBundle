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
        foreach ($table as $row) {
            if (null === $supplier = $supplierRepository->findOneBy(['name' => $row['supplier']])) {
                throw new \InvalidArgumentException("Failed to find the supplier named '{$row['supplier']}'.");
            }
            if (null === $currency = $currencyRepository->findOneBy(['code' => $row['currency']])) {
                throw new \InvalidArgumentException("Failed to find the currency for code '{$row['currency']}'.");
            }

            /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface $supplierOrder */
            $supplierOrder = $supplierOrderRepository->createNew();
            $supplierOrder
                ->setSupplier($supplier)
                ->setCurrency($currency)
                ->setNumber($row['number'])
                ->setPaymentTotal($row['paymentTotal']);

            if (isset($row['state'])) {
                $supplierOrder->setState($row['state']);
            }
            /* TODO if (isset($row['estimatedDateOfArrival'])) {
                $supplierOrder->setEstimatedDateOfArrival($row['state']);
            }*/

            $supplierOrders[] = $supplierOrder;
        }

        return $supplierOrders;
    }
}
