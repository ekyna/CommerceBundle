<?php

namespace Ekyna\Bundle\CommerceBundle\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;

/**
 * Class SupplierOrderItemContext
 * @package Ekyna\Bundle\CommerceBundle\Behat\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderItemContext implements Context, KernelAwareContext
{
    use KernelDictionary;

    /**
     * @Given /^The following supplier order items:$/
     *
     * @param TableNode $table
     */
    public function createSupplierOrderItems(TableNode $table)
    {
        $items = $this->castSupplierOrderItemsTable($table);

        $manager = $this->getContainer()->get('ekyna_commerce.supplier_order.manager');

        foreach ($items as $item) {
            $manager->persist($item);
        }

        $manager->flush();
        $manager->clear();
    }

    /**
     * @param TableNode $table
     *
     * @return array
     */
    private function castSupplierOrderItemsTable(TableNode $table)
    {
        $orderRepository = $this->getContainer()->get('ekyna_commerce.supplier_order.repository');
        $itemRepository = $this->getContainer()->get('ekyna_commerce.supplier_order_item.repository');
        $productRepository = $this->getContainer()->get('ekyna_commerce.supplier_product.repository');

        $items = [];
        foreach ($table as $row) {
            /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface $order */
            $order = $orderRepository->findOneBy(['number' => $row['order']]);
            if (null === $order) {
                throw new \InvalidArgumentException("Failed to find supplier order with number '{$row['order']}'.");
            }

            /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface $product */
            $product = $productRepository->findOneBy(['supplier'  => $order->getSupplier(), 'reference' => $row['reference']]);
            if (null === $product) {
                throw new \InvalidArgumentException("Supplier product with reference '{$row['reference']}' not found.");
            }

            /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface $item */
            $item = $itemRepository->createNew();
            $item
                ->setOrder($order)
                ->setProduct($product)
                ->setDesignation($product->getDesignation())
                ->setReference($product->getReference())
                ->setNetPrice($product->getNetPrice())
                ->setQuantity($row['quantity']);

            $items[] = $item;
        }

        return $items;
    }
}
