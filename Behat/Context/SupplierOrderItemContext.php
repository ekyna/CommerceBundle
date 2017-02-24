<?php

namespace Ekyna\Bundle\CommerceBundle\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;

/**
 * Class SupplierOrderItemContext
 * @package Ekyna\Bundle\CommerceBundle\Behat\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderItemContext implements Context, KernelAwareContext
{
    use KernelDictionary;

    /**
     * @Given /^The supplier order with number "(?P<number>[^"]+)" has the following items:$/
     *
     * @param string    $number
     * @param TableNode $table
     */
    public function createSupplierOrderItems($number, TableNode $table)
    {
        /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface $supplierOrder */
        $supplierOrder = $this->getContainer()
            ->get('ekyna_commerce.supplier_order.repository')
            ->findOneBy(['number' => $number]);

        if (null === $supplierOrder) {
            throw new \InvalidArgumentException("Supplier order with number '$number' not found.");
        }

        $items = $this->castSupplierOrderItemsTable($table, $supplierOrder->getSupplier());

        foreach ($items as $item) {
            $supplierOrder->addItem($item);
        }

        $manager = $this->getContainer()->get('ekyna_commerce.supplier_order.manager');

        $manager->persist($supplierOrder);
        $manager->flush();
        $manager->clear();
    }

    /**
     * @param TableNode $table
     * @param SupplierInterface $supplier
     *
     * @return array
     */
    private function castSupplierOrderItemsTable(TableNode $table, SupplierInterface $supplier)
    {
        $itemRepository = $this->getContainer()->get('ekyna_commerce.supplier_order_item.repository');
        $productRepository = $this->getContainer()->get('ekyna_commerce.supplier_product.repository');

        $items = [];
        foreach ($table as $row) {
            /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface $product */
            $product = $productRepository->findOneBy([
                'supplier'  => $supplier,
                'reference' => $reference = $row['reference'],
            ]);
            if (null === $product) {
                throw new \InvalidArgumentException("Supplier product with reference '$reference' not found.");
            }

            /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface $item */
            $item = $itemRepository->createNew();
            $item
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
