<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Product;

use Ekyna\Component\Commerce\Common\Model\AdjustmentModes;
use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Entity\OrderItem;
use Ekyna\Component\Commerce\Order\Entity\OrderItemAdjustment;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolverInterface;
use Ekyna\Component\Commerce\Product\Model\ProductInterface;
use Ekyna\Component\Commerce\Product\Model\ProductTypes;

/**
 * Class ItemBuilder
 * @package Ekyna\Bundle\CommerceBundle\Service\Product
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ItemBuilder
{
    const DATA_KEY_BUNDLE_SLOT = 'bundle_slot_id';

    /**
     * @var ProductResolver
     */
    private $productResolver;

    /**
     * @var TaxResolverInterface
     */
    private $taxResolver;


    /**
     * Builds the sale item from the product.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     * @param array             $data
     */
    public function buildItem(SaleItemInterface $item, ProductInterface $product, array $data = [])
    {
        switch($product->getType()) {
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
     * @param array             $data
     */
    protected function buildSimpleItem(SaleItemInterface $item, ProductInterface $product, array $data = [])
    {
        // TODO ProductTypes::assertSimple($product); OR variant

        $data = array_merge($item->getSubjectData(), $data);

        $data['provider'] = ProductProvider::NAME;
        $data['id'] = $product->getId();

        $item
            ->setSubjectData($data)
            ->setDesignation($product->getDesignation())
            ->setReference($product->getReference())
            ->setNetPrice($product->getNetPrice())
            ->setWeight($product->getWeight())
            ->setQuantity(1); // TODO preserve quantity ?

        $this->buildItemAdjustments($item, $product);
    }

    /**
     * Builds the sale item form the bundle product.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     * @param array             $data
     */
    protected function buildBundleItem(SaleItemInterface $item, ProductInterface $product, array $data = [])
    {
        ProductTypes::assertBundle($product);

        $data = array_merge($item->getSubjectData(), $data);

        $data['provider'] = ProductProvider::NAME;
        $data['id'] = $product->getId();

        // TODO $removeMissMatch = $data['remove_miss_match']; // Form the form (checkbox)
        // TODO unset($data['remove_miss_match']);

        // Bundle root item
        $item
            ->setSubjectData($data)
            ->setDesignation($product->getDesignation())
            ->setReference($product->getReference())
            ->setQuantity(1); // TODO preserve quantity ?

        // TODO every slot must match a single item
        $bundleProducts = [];
        foreach ($product->getBundleSlots() as $bundleSlot) {
            /** @var \Ekyna\Component\Commerce\Product\Model\BundleChoiceInterface $bundleChoice */
            $bundleChoice = $bundleSlot->getChoices()->first();
            $bundleProduct = $bundleChoice->getProduct();
            $bundleProducts[] = $bundleProduct;

            // Find matching item
            $found = false;
            foreach ($item->getChildren() as $child) {
                $childData = $child->getSubjectData();
                if (!array_key_exists(self::DATA_KEY_BUNDLE_SLOT, $childData)) {
                    continue;
                }
                if ($childData[self::DATA_KEY_BUNDLE_SLOT] != $bundleSlot->getId()) {
                    continue;
                }

                // The child item matches the bundle slot

                $product = $this->productResolver->supports($child);

                if ($bundleProduct === $product) {
                    $found = true;
                    // TODO $item->setPosition($bundleSlot->getPosition());
                    // TODO $item->setQuantity($bundleSlot->getMinQuantity());
                } else { // TODO if ($removeMissMatch)
                    $item->removeChild($child);
                }

                break;
            }

            // If not found, create an item with the slot product as subject
            if (!$found) {
                // TODO gonna need a factory there ...
                $childItem = new OrderItem();
                $item->addChild($childItem);

                $this->buildSimpleItem($childItem, $bundleProduct, [
                    self::DATA_KEY_BUNDLE_SLOT => $bundleSlot->getId()
                ]);

                $childItem
                    ->setPosition($bundleSlot->getPosition())
                    ->setQuantity($bundleSlot->getMinQuantity());
            }
        }

        // TODO remove items who does not match a bundle product (???)
        // TODO if ($removeMissMatch) {
        /*foreach ($item->getChildren() as $child) {
            $childProduct = $this->productResolver->resolve($child);

            if (null === $childProduct || !in_array($childProduct, $bundleProducts)) {
                $item->removeChild($child);
            }
        }*/
    }

    /**
     * Builds the sale item form the configurable product.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     * @param array             $data
     */
    protected function buildConfigurableItem(SaleItemInterface $item, ProductInterface $product, array $data = [])
    {
        ProductTypes::assertConfigurable($product);

        $data = array_merge($item->getSubjectData(), $data);

        $data['provider'] = ProductProvider::NAME;
        $data['id'] = $product->getId();

        // TODO $removeMissMatch = $data['remove_miss_match']; // Form the form (checkbox)
        // TODO unset($data['remove_miss_match']);

        /**
         * Configuration format : TODO validation
         *
         * $data['configuration'][$slotId] = [
         *     'choice_id' => $choiceId,
         *     'quantity'  => $quantity,
         * ]
         */
        if (!array_key_exists('configuration', $data)) {
            throw new InvalidArgumentException("Unexpected item data : missing 'configuration' key.");
        }
        $configuration = $data['configuration'];

        // Configurable root item
        $item
            ->setSubjectData($data)
            ->setDesignation($product->getDesignation())
            ->setReference($product->getReference())
            ->setQuantity(1); // TODO preserve quantity ?

        // TODO every slot must match a single item
        $bundleProducts = [];
        foreach ($product->getBundleSlots() as $bundleSlot) {
            // Extract slot configuration
            if (!array_key_exists($bundleSlot->getId(), $configuration)) {
                throw new InvalidArgumentException(sprintf("Slot '%d' is not configured.", $bundleSlot->getId()));
            }
            $slotConfiguration = $configuration[$bundleSlot->getId()];

            // Extract slot choice configuration
            if (!array_key_exists('choice_id', $slotConfiguration)) {
                throw new InvalidArgumentException(sprintf("Slot '%d' choice is not configured.", $bundleSlot->getId()));
            }
            $selectedBundleChoice = null;
            foreach ($bundleSlot->getChoices() as $bundleChoice) {
                if ($bundleChoice->getId() === $slotConfiguration['choice_id']) { // TODO
                    $selectedBundleChoice = $bundleChoice;
                }
            }
            if (null === $selectedBundleChoice) {
                throw new InvalidArgumentException("Configurable bundle choice not found.");
            }

            $bundleProduct = $selectedBundleChoice->getProduct();
            $bundleProducts[] = $bundleProduct;

            // Find matching item
            $found = false;
            foreach ($item->getChildren() as $child) {
                $childData = $child->getSubjectData();
                if (!array_key_exists(self::DATA_KEY_BUNDLE_SLOT, $childData)) {
                    continue;
                }
                if ($childData[self::DATA_KEY_BUNDLE_SLOT] != $bundleSlot->getId()) {
                    continue;
                }

                // The child item matches the bundle slot

                $product = $this->productResolver->supports($child);

                if ($bundleProduct === $product) {
                    $found = true;
                    // TODO $item->setPosition($bundleSlot->getPosition());
                    // TODO $item->setQuantity($slotConfiguration['quantity']);
                } else { // TODO if ($removeMissMatch)
                    $item->removeChild($child);
                }

                break;
            }

            // If not found, create an item with the slot product as subject
            if (!$found) {
                // TODO gonna need a factory there ...
                $childItem = new OrderItem();
                $item->addChild($childItem);

                $this->buildSimpleItem($childItem, $bundleProduct, [
                    self::DATA_KEY_BUNDLE_SLOT => $bundleSlot->getId()
                ]);

                $childItem
                    ->setPosition($bundleSlot->getPosition())
                    ->setQuantity($slotConfiguration['quantity']);
            }
        }

        // TODO remove items who does not match a bundle product (???)
        // TODO if ($removeMissMatch) {
        /*foreach ($item->getChildren() as $child) {
            $childProduct = $this->productResolver->resolve($child);

            if (null === $childProduct || !in_array($childProduct, $bundleProducts)) {
                $item->removeChild($child);
            }
        }*/
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
