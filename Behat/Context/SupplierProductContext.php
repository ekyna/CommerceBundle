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
    public function createSupplierProducts(TableNode $table)
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
        /** @var \Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface $acmeProductRepository */
        $acmeProductRepository = $this->getContainer()->get('acme_product.product.repository');
        $subjectHelper = $this->getContainer()->get('ekyna_commerce.subject_helper');
        $supplierRepository = $this->getContainer()->get('ekyna_commerce.supplier.repository');
        $supplierProductRepository = $this->getContainer()->get('ekyna_commerce.supplier_product.repository');

        $supplierProducts = [];
        foreach ($table as $row) {
            if (null === $supplier = $supplierRepository->findOneBy(['name' => $row['supplier']])) {
                throw new \InvalidArgumentException("Failed to find the supplier named '{$row['supplier']}'.");
            }

            /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface $product */
            $product = $supplierProductRepository->createNew();

            if (isset($row['acme_product'])) {
                $acmeProduct = $acmeProductRepository->findOneBy(['reference' => $row['acme_product']]);
                if (null === $acmeProduct) {
                    throw new \InvalidArgumentException(
                        "Failed to find the acme product with reference '{$row['acme_product']}'."
                    );
                }
                $subjectHelper->assign($product, $acmeProduct);
            }

            /*$provider = isset($row['provider']) ? $row['provider'] : null;
            $identifier = isset($row['identifier']) ? $row['identifier'] : null;
            if (!empty($provider) && !empty($identifier)) {
                $product
                    ->getSubjectIdentity()
                    ->setProvider($provider)
                    ->setIdentifier($identifier);
            }*/

            $eda = null;
            if (isset($row['eda']) && 0 < strlen($row['eda'])) {
                $eda = new \DateTime($row['eda']);
            }

            $product
                ->setSupplier($supplier)
                ->setDesignation($row['designation'])
                ->setReference($row['reference'])
                ->setNetPrice($row['price'])
                ->setWeight($row['weight'])
                ->setAvailableStock($row['available'])
                ->setOrderedStock($row['ordered'])
                ->setEstimatedDateOfArrival($eda);

            $supplierProducts[] = $product;
        }

        return $supplierProducts;
    }
}
