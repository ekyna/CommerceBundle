<?php

namespace Ekyna\Bundle\CommerceBundle\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;

/**
 * Class SupplierDeliveryContext
 * @package Ekyna\Bundle\CommerceBundle\Behat\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierDeliveryContext implements Context, KernelAwareContext
{
    use KernelDictionary;

    /**
     * @Given The following supplier deliveries:
     *
     * @param TableNode $table
     */
    public function createSupplierDeliveries(TableNode $table)
    {
        $deliveries = $this->castSupplierDeliveriesTable($table);

        $manager = $this->getContainer()->get('ekyna_commerce.supplier_delivery.manager');

        foreach ($deliveries as $delivery) {
            $manager->persist($delivery);
        }

        $manager->flush();
        $manager->clear();
    }

    /**
     * @param TableNode $table
     *
     * @return array
     */
    private function castSupplierDeliveriesTable(TableNode $table)
    {
        $orderRepository = $this->getContainer()->get('ekyna_commerce.supplier_order.repository');
        $deliveryRepository = $this->getContainer()->get('ekyna_commerce.supplier_delivery.repository');

        $deliveries = [];
        foreach ($table as $row) {
            if (null === $order = $orderRepository->findOneBy(['number' => $row['order']])) {
                throw new \InvalidArgumentException("Failed to find the supplier order with number '{$row['order']}'.");
            }

            /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierDeliveryInterface $delivery */
            $delivery = $deliveryRepository->createNew();
            $delivery->setOrder($order);

            $deliveries[] = $delivery;
        }

        return $deliveries;
    }
}
