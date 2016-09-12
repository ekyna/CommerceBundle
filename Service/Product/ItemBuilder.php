<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Product;

use Ekyna\Component\Commerce\Common\Model\AdjustmentModes;
use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Order\Entity\OrderItemAdjustment;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolverInterface;
use Ekyna\Component\Commerce\Product\Model\BundleSlotInterface;
use Ekyna\Component\Commerce\Product\Model\ProductInterface;
use Ekyna\Component\Commerce\Product\Model\ProductTypes;

/**
 * Class ItemBuilder
 * @package Ekyna\Bundle\CommerceBundle\Service\Product
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ItemBuilder
{
    const DATA_KEY_REMOVE_MISS_MATCH = 'remove_miss_match';

    /**
     * @var TaxResolverInterface
     */
    private $taxResolver;


    /**
     * Constructor.
     *
     * @param TaxResolverInterface $taxResolver
     */
    public function __construct(TaxResolverInterface $taxResolver)
    {
        $this->taxResolver = $taxResolver;
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
    public function setItemProduct(SaleItemInterface $item, ProductInterface $product, array $extraData = [])
    {
        if ((null === $subject = $item->getSubject()) || $product != $subject) {
            $item->setSubject($product);
        }

        $data = array_replace((array)$item->getSubjectData(), $extraData, [
            'provider' => ProductProvider::NAME,
            'id'       => $product->getId(),
        ]);

        $item->setSubjectData($data);

        return $data;
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

        $this->buildItemAdjustments($item, $product);
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

        $this->buildItemAdjustments($item, $product);

        // Every slot must match a single item
        $bundleProducts = [];
        foreach ($product->getBundleSlots() as $bundleSlot) {
            /** @var \Ekyna\Component\Commerce\Product\Model\BundleChoiceInterface $bundleChoice */
            $bundleChoice = $bundleSlot->getChoices()->first();
            $bundleProduct = $bundleChoice->getProduct();
            $bundleProducts[] = $bundleProduct;

            // Find matching item
            foreach ($item->getChildren() as $child) {
                $bundleSlotId = intval($child->getSubjectData(BundleSlotInterface::ITEM_DATA_KEY));
                if ($bundleSlotId != $bundleSlot->getId()) {
                    continue;
                }

                /** @var ProductInterface $childItemProduct */
                $childItemProduct = $item->getSubject();

                // Sets the bundle product
                $this->buildItem($child, $childItemProduct, [
                    self::DATA_KEY_REMOVE_MISS_MATCH => $removeMissMatch,
                ]);
                // Done by prepareItem()  //$item->setPosition($bundleSlot->getPosition());

                continue 2;
            }

            // Not found : call prepareItem() first.
            throw new RuntimeException("Bundle slot matching item not found.");
        }

        // TODO remove bundle slots duplicates

        // Removes miss match items
        if ($removeMissMatch) {
            foreach ($item->getChildren() as $child) {
                $childProduct = $child->getSubject();
                if (null === $childProduct || !in_array($childProduct, $bundleProducts)) {
                    $item->removeChild($child);
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
            ->setReference($product->getReference());

        $this->buildItemAdjustments($item, $product);

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
                // Done by prepareItem()  //$item->setPosition($bundleSlot->getPosition());

                continue 2;
            }

            // Not found : call prepareItem() first.
            throw new RuntimeException("Bundle slot matching item not found.");
        }

        // TODO remove bundle slots duplicates ?

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
     * Builds the item adjustments.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     */
    protected function buildItemAdjustments(SaleItemInterface $item, ProductInterface $product)
    {
        $sale = $item->getSale();
        $customer = $sale->getCustomer();
        $address = $sale->getDeliveryAddress();
        $taxGroup = $product->getTaxGroup();

        if (null !== $customer && null !== $address) {
            $taxes = $this->taxResolver->getApplicableTaxesByTaxGroupAndCustomerGroups(
                $taxGroup, $customer->getCustomerGroups(), $address
            );

            // TODO $this->adjustmentFactory->buildTaxationAdjustments($item, $taxes);

            // TODO temporary
            foreach ($taxes as $tax) {
                $adjustment = new OrderItemAdjustment();
                $adjustment
                    ->setMode(AdjustmentModes::MODE_PERCENT)
                    ->setType(AdjustmentTypes::TYPE_TAXATION)
                    ->setDesignation($tax->getName())
                    ->setAmount($tax->getRate());

                /** @var \Ekyna\Component\Commerce\Order\Model\OrderItemInterface $item */
                $item->addAdjustment($adjustment);
            }
        }
    }
}
