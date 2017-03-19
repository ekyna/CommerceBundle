<?php

namespace Ekyna\Bundle\CommerceBundle\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;

/**
 * Class SupplierDeliveryItemContext
 * @package Ekyna\Bundle\CommerceBundle\Behat\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierDeliveryItemContext implements Context, KernelAwareContext
{
    use KernelDictionary;

    /**
     * @Given /^The following supplier delivery items:$/
     *
     * @param TableNode $table
     */
    public function createSupplierDeliveryItems(TableNode $table)
    {
        $items = $this->castSupplierDeliveryItemsTable($table);

        $manager = $this->getContainer()->get('ekyna_commerce.supplier_delivery_item.manager');

        foreach ($items as $item) {
            $manager->persist($item);
        }

        $manager->flush();
        $manager->clear();
    }

    /**
     * @param TableNode              $table
     *
     * @return array
     */
    private function castSupplierDeliveryItemsTable(TableNode $table)
    {
        $orderRepository = $this->getContainer()->get('ekyna_commerce.supplier_order.repository');
        $itemRepository = $this->getContainer()->get('ekyna_commerce.supplier_delivery_item.repository');

        $items = [];
        foreach ($table as $row) {
            /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface $order */
            $order = $orderRepository->findOneBy(['number' => $row['order']]);
            if (null === $order) {
                throw new \InvalidArgumentException("Failed to find supplier order with number '{$row['order']}'.");
            }

            if (null === $delivery = $order->getDeliveries()->get($row['delivery'])) {
                throw new \InvalidArgumentException("Failed to fetch supplier delivery at index '{$row['delivery']}'.");
            }

            $reference = $row['reference'];

            $orderItem = null;
            foreach ($order->getItems() as $oi) {
                if ($oi->getReference() === $reference) {
                    $orderItem = $oi;
                    break;
                }
            }
            if (null === $orderItem) {
                throw new \InvalidArgumentException("Supplier order item not found for reference '$reference'.");
            }

            /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierDeliveryItemInterface $item */
            $item = $itemRepository->createNew();
            $item
                ->setDelivery($delivery)
                ->setOrderItem($orderItem)
                ->setQuantity($row['quantity']);

            $items[] = $item;
        }

        return $items;
    }
}
