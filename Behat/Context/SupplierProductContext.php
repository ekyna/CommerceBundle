<?php

namespace Ekyna\Bundle\CommerceBundle\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;

/**
 * Class SupplierProductContext
 * @package Ekyna\Bundle\CommerceBundle\Behat\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierProductContext implements Context, KernelAwareContext
{
    use KernelDictionary;

    /**
     * @Given The following supplier products:
     *
     * @param TableNode $table
     */
    public function createSupplierProduct(TableNode $table)
    {
        $supplierProducts = $this->castSupplierProductsTable($table);

        $manager = $this->getContainer()->get('ekyna_commerce.supplier_product.manager');

        foreach ($supplierProducts as $supplierProduct) {
            $manager->persist($supplierProduct);
        }

        $manager->flush();
        $manager->clear();
    }

    /**
     * @param TableNode $table
     *
     * @return array
     */
    private function castSupplierProductsTable(TableNode $table)
    {
        $supplierRepository = $this->getContainer()->get('ekyna_commerce.supplier.repository');
        $supplierProductRepository = $this->getContainer()->get('ekyna_commerce.supplier_product.repository');

        $supplierProducts = [];
        foreach ($table->getHash() as $hash) {
            if (null === $supplier = $supplierRepository->findOneBy(['name' => $hash['supplier']])) {
                throw new \InvalidArgumentException("Failed to find the supplier named '{$hash['supplier']}'.");
            }

            $eda = 0 < strlen($hash['eda']) ? new \DateTime($hash['eda']) : null;

            /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface $product */
            $product = $supplierProductRepository->createNew();
            $product
                ->setSupplier($supplier)
                ->setDesignation($hash['designation'])
                ->setReference($hash['reference'])
                ->setNetPrice($hash['price'])
                ->setWeight($hash['weight'])
                ->setAvailableStock($hash['available'])
                ->setOrderedStock($hash['ordered'])
                ->setEstimatedDateOfArrival($eda);

            $supplierProducts[] = $product;
        }

        return $supplierProducts;
    }
}
