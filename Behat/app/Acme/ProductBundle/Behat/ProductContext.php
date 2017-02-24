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
        $productRepository = $this->getContainer()->get('acme_product.product.repository');

        $products = [];
        foreach ($table->getHash() as $hash) {
            /** @var \Acme\ProductBundle\Entity\Product $product */
            $product = $productRepository->createNew();
            $product
                ->setDesignation($hash['designation'])
                ->setReference($hash['reference'])
                ->setNetPrice($hash['price'])
                ->setWeight($hash['weight'])
                ->setStockMode(StockSubjectModes::MODE_ENABLED)
            ;

            $products[] = $product;
        }

        return $products;
    }
}
