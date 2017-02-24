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
        foreach ($table as $row) {
            if (null === $supplier = $supplierRepository->findOneBy(['name' => $row['supplier']])) {
                throw new \InvalidArgumentException("Failed to find the supplier named '{$row['supplier']}'.");
            }

            $eda = 0 < strlen($row['eda']) ? new \DateTime($row['eda']) : null;

            /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface $product */
            $product = $supplierProductRepository->createNew();
            $product
                ->setSupplier($supplier)
                ->setDesignation($row['designation'])
                ->setReference($row['reference'])
                ->setNetPrice($row['price'])
                ->setWeight($row['weight'])
                ->setAvailableStock($row['available'])
                ->setOrderedStock($row['ordered'])
                ->setEstimatedDateOfArrival($eda);

            $provider = isset($row['provider']) ? $row['provider'] : null;
            $identifier = isset($row['identifier']) ? $row['identifier'] : null;
            if (!(empty($provider) && empty($identifier))) {
                $product
                    ->getSubjectIdentity()
                    ->setProvider($provider)
                    ->setIdentifier($identifier);
            }

            $supplierProducts[] = $product;
        }

        return $supplierProducts;
    }
}
