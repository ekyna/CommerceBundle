<?php

namespace Acme\ProductBundle\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;

/**
 * Class ProductContext
 * @package Acme\ProductBundle\Behat
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductContext implements Context, KernelAwareContext
{
    use KernelDictionary;

    /**
     * @Given The following acme products:
     *
     * @param TableNode $table
     */
    public function createProducts(TableNode $table)
    {
        $products = $this->castProductsTable($table);

        $manager = $this->getContainer()->get('acme_product.product.manager');

        foreach ($products as $product) {
            $manager->persist($product);
        }

        $manager->flush();
        $manager->clear();
    }

    /**
     * @param TableNode $table
     *
     * @return array
     */
    private function castProductsTable(TableNode $table)
    {
        $taxGroupRepository = $this->getContainer()->get('ekyna_commerce.tax_group.repository');
        $productRepository = $this->getContainer()->get('acme_product.product.repository');

        $products = [];
        foreach ($table->getHash() as $hash) {
            /** @var \Acme\Product\Entity\Product $product */
            $product = $productRepository->createNew();
            $product
                ->setDesignation($hash['designation'])
                ->setReference($hash['reference'])
                ->setNetPrice($hash['price'])
                ->setWeight($hash['weight'])
                ->setStockMode(StockSubjectModes::MODE_AUTO)
            ;

            if (isset($row['taxGroup'])) {
                if (null === $taxGroup = $taxGroupRepository->findOneBy(['name' => $row['taxGroup']])) {
                    throw new \InvalidArgumentException("Failed to find the tax group with name '{$row['taxGroup']}'.");
                }
                $product->setTaxGroup($taxGroup);
            } else {
                $product->setTaxGroup($taxGroupRepository->findDefault());
            }

            $products[] = $product;
        }

        return $products;
    }
}
