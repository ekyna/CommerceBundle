<?php

namespace Ekyna\Bundle\CommerceBundle\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Ekyna\Component\Commerce\Supplier\Model as Supplier;

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
    public function orderIsSubmitted($number)
    {
        $this->submitOrder($this->findOrderByNumber($number));
    }

    /**
     * @Given /^The supplier order with number "(?P<number>[^"]+)" is received$/
     *
     * @param string $number
     */
    public function orderIsReceived($number)
    {
        $this->deliverOrder($this->findOrderByNumber($number));
    }

    /**
     * @Given The following supplier orders:
     *
     * @param TableNode $table
     */
    public function createOrders(TableNode $table)
    {
        $supplierOrders = $this->castOrdersTable($table);

        $manager = $this->getContainer()->get('ekyna_commerce.supplier_order.manager');

        foreach ($supplierOrders as $supplierOrder) {
            $manager->persist($supplierOrder);
        }

        $manager->flush();
        $manager->clear();
    }

    /**
     * Finds the order by its number.
     *
     * @param string $number
     *
     * @return Supplier\SupplierOrderInterface
     */
    public function findOrderByNumber($number)
    {
        /** @var Supplier\SupplierOrderInterface $order */
        $order = $this->getContainer()
            ->get('ekyna_commerce.supplier_order.repository')
            ->findOneBy(['number' => $number]);

        if (null === $order) {
            throw new \InvalidArgumentException("Failed to find supplier order with number '$number'.");
        }

        return $order;
    }

    /**
     * Submit the given order.
     *
     * @param Supplier\SupplierOrderInterface $order
     */
    private function submitOrder(Supplier\SupplierOrderInterface $order)
    {
        // TODO use a service (workflow ?) (same in admin controller)
        $order->setOrderedAt(new \DateTime());

        $manager = $this->getContainer()->get('ekyna_commerce.supplier_order.manager');
        $manager->persist($order);
        $manager->flush();
        $manager->clear();
    }

    /**
     * Deliver the given order.
     *
     * @param Supplier\SupplierOrderInterface $order
     */
    private function deliverOrder(Supplier\SupplierOrderInterface $order)
    {
        $class = $this->getContainer()->getParameter('ekyna_commerce.supplier_delivery.class');
        $itemClass = $this->getContainer()->getParameter('ekyna_commerce.supplier_delivery_item.class');

        /** @var Supplier\SupplierDeliveryInterface $delivery */
        $delivery = new $class;

        foreach ($order->getItems() as $item) {
            /** @var Supplier\SupplierDeliveryItemInterface $deliveryItem */
            $deliveryItem = new $itemClass;
            $deliveryItem
                ->setOrderItem($item)
                ->setQuantity($item->getQuantity());

            $delivery->addItem($deliveryItem);
        }

        $order->addDelivery($delivery);

        $manager = $this->getContainer()->get('ekyna_commerce.supplier_delivery.manager');
        $manager->persist($delivery);
        $manager->flush();
        $manager->clear();
    }

    /**
     * @param TableNode $table
     *
     * @return array
     */
    private function castOrdersTable(TableNode $table)
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
                ->setShippingCost(isset($row['shippingCost']) ? $row['shippingCost'] : 0);

            if (isset($row['estimatedDateOfArrival'])) {
                $supplierOrder->setEstimatedDateOfArrival(new \DateTime($row['estimatedDateOfArrival']));
            }

            $supplierOrders[] = $supplierOrder;
        }

        return $supplierOrders;
    }
}
