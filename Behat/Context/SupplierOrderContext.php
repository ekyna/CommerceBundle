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
     * @Given /^The supplier order with number "(?P<number>[^"]+)" is submitted$/
     *
     * @param string $number
     */
    public function setSupplierOrderState($number)
    {
        /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface $order */
        $order = $this->getContainer()
            ->get('ekyna_commerce.supplier_order.repository')
            ->findOneBy(['number' => $number]);

        if (null === $order) {
            throw new \InvalidArgumentException("Supplier order with number '$number' not found.");
        }

        // TODO use a service (workflow ?) (same in admin controller)
        $order->setOrderedAt(new \DateTime());

        $manager = $this->getContainer()->get('ekyna_commerce.supplier_order.manager');
        $manager->persist($order);
        $manager->flush();
        $manager->clear();
    }

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

            if (isset($row['estimatedDateOfArrival'])) {
                $supplierOrder->setEstimatedDateOfArrival(new \DateTime($row['estimatedDateOfArrival']));
            }

            $supplierOrders[] = $supplierOrder;
        }

        return $supplierOrders;
    }
}
