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
     * @Given /^The supplier delivery with id "(?P<id>[^"]+)" has the following items:$/
     *
     * @param int       $id
     * @param TableNode $table
     */
    public function createSupplierDeliveryItems($id, TableNode $table)
    {
        /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierDeliveryInterface $delivery */
        $delivery = $this->getContainer()
            ->get('ekyna_commerce.supplier_delivery.repository')
            ->find($id);

        if (null === $delivery) {
            throw new \InvalidArgumentException("Supplier delivery with id '$id' not found.");
        }

        $items = $this->castSupplierDeliveryItemsTable($table, $delivery->getOrder());

        foreach ($items as $item) {
            $delivery->addItem($item);
        }

        $manager = $this->getContainer()->get('ekyna_commerce.supplier_delivery.manager');

        $manager->persist($delivery);
        $manager->flush();
        $manager->clear();
    }

    /**
     * @param TableNode              $table
     * @param SupplierOrderInterface $order
     *
     * @return array
     */
    private function castSupplierDeliveryItemsTable(TableNode $table, SupplierOrderInterface $order)
    {
        $itemRepository = $this->getContainer()->get('ekyna_commerce.supplier_delivery_item.repository');

        $items = [];
        foreach ($table as $row) {
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
                ->setOrderItem($orderItem)
                ->setQuantity($row['quantity']);

            $items[] = $item;
        }

        return $items;
    }
}
