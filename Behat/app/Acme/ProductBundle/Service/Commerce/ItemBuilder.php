<?php

namespace Acme\ProductBundle\Service\Commerce;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Subject\Builder\ItemBuilderInterface;

/**
 * Class ItemBuilder
 * @package Acme\ProductBundle\Service\Commerce
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ItemBuilder implements ItemBuilderInterface
{
    /**
     * @var ProductProvider
     */
    private $provider;


    /**
     * Constructor.
     *
     * @param ProductProvider $provider
     */
    public function __construct(ProductProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @inheritdoc
     */
    public function initializeItem(SaleItemInterface $item)
    {

    }

    /**
     * @inheritdoc
     */
    public function buildItem(SaleItemInterface $item)
    {
        $product = $this->provider->resolve($item);

        $this->provider->assign($item, $product);

        $item
            ->setDesignation($product->getDesignation())
            ->setReference($product->getReference())
            ->setNetPrice($product->getNetPrice())
            ->setWeight($product->getWeight())
            ->setTaxGroup($product->getTaxGroup());
    }

    /**
     * @inheritDoc
     */
    public function buildAdjustmentsData(SaleItemInterface $item)
    {
        return [];
    }
}
