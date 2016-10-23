<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Product;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Product\Model\BundleSlotInterface;
use Ekyna\Component\Commerce\Product\Model\ProductInterface;
use Ekyna\Component\Commerce\Product\Model\ProductTypes;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface;

/**
 * Class ItemBuilder
 * @package Ekyna\Bundle\CommerceBundle\Service\Product
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ItemBuilder
{
    const DATA_KEY_REMOVE_MISS_MATCH = 'remove_miss_match';

    /**
     * @var SaleFactoryInterface
     */
    private $saleFactory;


    /**
     * Constructor.
     *
     * @param SaleFactoryInterface       $saleFactory
     */
    public function __construct(SaleFactoryInterface $saleFactory)
    {
        $this->saleFactory = $saleFactory;
    }

    /**
     * Builds the sale item from the product.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     * @param array             $data
     */
    public function buildItem(SaleItemInterface $item, ProductInterface $product, array $data = [])
    {
        switch ($product->getType()) {
            case ProductTypes::TYPE_SIMPLE:
            case ProductTypes::TYPE_VARIANT:
                $this->buildSimpleItem($item, $product, $data);
                break;
            case ProductTypes::TYPE_BUNDLE:
                $this->buildBundleItem($item, $product, $data);
                break;
            case ProductTypes::TYPE_CONFIGURABLE:
                $this->buildConfigurableItem($item, $product, $data);
                break;
            default:
                throw new InvalidArgumentException('Unexpected product type');
        }
    }

    /**
     * Builds the sale item form the simple product.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     * @param array             $extraData
     */
    protected function buildSimpleItem(SaleItemInterface $item, ProductInterface $product, array $extraData = [])
    {
        if (!in_array($product->getType(), [ProductTypes::TYPE_SIMPLE, ProductTypes::TYPE_VARIANT])) {
            throw new InvalidArgumentException("Unexpected product type.");
        }

        $this->setItemProduct($item, $product, $extraData);

        $item
            ->setDesignation($product->getDesignation())
            ->setReference($product->getReference())
            ->setNetPrice($product->getNetPrice())
            ->setWeight($product->getWeight());
    }

    /**
     * Builds the sale item form the bundle product.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     * @param array             $extraData
     */
    protected function buildBundleItem(SaleItemInterface $item, ProductInterface $product, array $extraData = [])
    {
        ProductTypes::assertBundle($product);

        $this->setItemProduct($item, $product, $extraData);

        // Remove miss match option
        $removeMissMatch = (bool)$item->getSubjectData(self::DATA_KEY_REMOVE_MISS_MATCH);

        // Bundle root item
        $item
            ->unsetSubjectData(self::DATA_KEY_REMOVE_MISS_MATCH)
            ->setDesignation($product->getDesignation())
            ->setReference($product->getReference());

        // Every slot must match a single item
        $bundleProducts = [];
        foreach ($product->getBundleSlots() as $bundleSlot) {
            /** @var \Ekyna\Component\Commerce\Product\Model\BundleChoiceInterface $bundleChoice */
            $bundleChoice = $bundleSlot->getChoices()->first();
            $bundleProduct = $bundleChoice->getProduct();
            $bundleProducts[] = $bundleProduct;

            // Find matching item
            foreach ($item->getChildren() as $childItem) {
                $bundleSlotId = intval($childItem->getSubjectData(BundleSlotInterface::ITEM_DATA_KEY));
                if ($bundleSlotId != $bundleSlot->getId()) {
                    continue;
                }

                /** @var ProductInterface $childItemProduct */
                $childItemProduct = $item->getSubject();

                // Build the item form the bundle choice's product
                $this->buildItem($childItem, $childItemProduct, [
                    self::DATA_KEY_REMOVE_MISS_MATCH => $removeMissMatch,
                ]);

                // Item is immutable
                $childItem->setImmutable(true);

                continue 2;
            }

            // Not found : call prepareItem() first.
            throw new RuntimeException("Bundle slot matching item not found.");
        }

        // TODO Cleanup : remove bundle slots duplicates

        // Removes miss match items
        if ($removeMissMatch) {
            foreach ($item->getChildren() as $childItem) {
                $childProduct = $childItem->getSubject();
                if (null === $childProduct || !in_array($childProduct, $bundleProducts)) {
                    $item->removeChild($childItem);
                }
            }
        }
    }

    /**
     * Builds the sale item form the configurable product.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     * @param array             $extraData
     */
    protected function buildConfigurableItem(SaleItemInterface $item, ProductInterface $product, array $extraData = [])
    {
        ProductTypes::assertConfigurable($product);

        $this->setItemProduct($item, $product, $extraData);

        // Remove miss match option
        $removeMissMatch = (bool)$item->getSubjectData(self::DATA_KEY_REMOVE_MISS_MATCH);

        // Configurable root item
        $item
            ->unsetSubjectData(self::DATA_KEY_REMOVE_MISS_MATCH)
            ->setDesignation($product->getDesignation())
            ->setReference($product->getReference())
            ->setConfigurable(true);

        // Every slot must match a single item
        $bundleProducts = [];
        foreach ($product->getBundleSlots() as $bundleSlot) {
            // Find matching item
            foreach ($item->getChildren() as $childItem) {
                $bundleSlotId = intval($childItem->getSubjectData(BundleSlotInterface::ITEM_DATA_KEY));
                if ($bundleSlotId != $bundleSlot->getId()) {
                    continue;
                }

                /** @var ProductInterface $childItemProduct */
                $childItemProduct = $childItem->getSubject();

                // Sets the bundle product
                $this->buildItem($childItem, $childItemProduct, [
                    self::DATA_KEY_REMOVE_MISS_MATCH => $removeMissMatch,
                ]);

                // Item is immutable
                $childItem->setImmutable(true);

                continue 2;
            }

            // Not found : call prepareItem() first.
            throw new RuntimeException("Bundle slot matching item not found.");
        }

        // TODO Cleanup : remove bundle slots duplicates ?

        // Removes miss match items
        if ($removeMissMatch) {
            foreach ($item->getChildren() as $childItem) {
                $childProduct = $childItem->getSubject();

                if (null === $childProduct || !in_array($childProduct, $bundleProducts)) {
                    $item->removeChild($childItem);
                }
            }
        }
    }

    /**
     * Sets the item product (subject) and configures the item subject data.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     * @param array             $extraData
     *
     * @return array The resulting item subject data.
     */
    protected function setItemProduct(SaleItemInterface $item, ProductInterface $product, array $extraData = [])
    {
        if ((null === $subject = $item->getSubject()) || $product != $subject) {
            $item->setSubject($product);
        }

        $data = array_replace((array)$item->getSubjectData(), $extraData, [
            SubjectProviderInterface::DATA_KEY => ProductProvider::NAME,
            'id'                               => $product->getId(),
        ]);

        $item->setSubjectData($data);

        return $data;
    }
}
