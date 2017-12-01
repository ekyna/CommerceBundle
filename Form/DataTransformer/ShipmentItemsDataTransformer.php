<?php

namespace Ekyna\Bundle\CommerceBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Shipment\Builder\ShipmentBuilderInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class ShipmentItemsDataTransformer
 * @package Ekyna\Bundle\CommerceBundle\Form\DataTransformer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentItemsDataTransformer implements DataTransformerInterface
{
    /**
     * @var ShipmentBuilderInterface
     */
    private $builder;


    /**
     * Constructor.
     *
     * @param ShipmentBuilderInterface $builder
     */
    public function __construct(ShipmentBuilderInterface $builder)
    {
        $this->builder = $builder;
    }

    /**
     * Transforms the flat shipment items collection into a tree shipment items collection.
     *
     * @param ShipmentInterface $shipment
     *
     * @return ShipmentInterface
     */
    public function transform($shipment)
    {
        if (!$shipment instanceof ShipmentInterface) {
            throw new TransformationFailedException("Expected instance of " . ShipmentInterface::class);
        }

        $this->builder->build($shipment);

        $flat = new ArrayCollection($shipment->getItems()->toArray());
        $tree = new ArrayCollection();

        // Move shipment items from flat to tree for each sale items
        foreach ($shipment->getSale()->getItems() as $saleItem) {
            $this->buildTreeShipmentItem($saleItem, $flat, $tree);
        }

        // Replace shipment items
        $shipment->setItems($tree);

        return $shipment;
    }

    /**
     * Builds the tree shipment item.
     *
     * @param SaleItemInterface $saleItem
     * @param ArrayCollection   $flat
     * @param ArrayCollection   $parent
     */
    private function buildTreeShipmentItem(SaleItemInterface $saleItem, ArrayCollection $flat, ArrayCollection $parent)
    {
        $shipmentItem = null;

        // Skip compound sale items with only public children
        if (!($saleItem->isCompound() && !$saleItem->hasPrivateChildren())) {
            // Look for an existing shipment item
            /** @var ShipmentItemInterface $item */
            foreach ($flat as $item) {
                if ($item->getSaleItem() === $saleItem) {
                    $shipmentItem = $item->clearChildren();
                    break;
                }
            }
        }

        $addTo = null !== $shipmentItem ? $shipmentItem->getChildren() : $parent;

        foreach ($saleItem->getChildren() as $childSaleItem) {
            $this->buildTreeShipmentItem($childSaleItem, $flat, $addTo);
        }

        if (null !== $shipmentItem) {
            $parent->add($shipmentItem);
        }
    }

    /**
     * Transforms the tree shipment items collection into a flat shipment items collection.
     *
     * @param ShipmentInterface $shipment
     *
     * @return ShipmentInterface
     */
    public function reverseTransform($shipment)
    {
        if (!$shipment instanceof ShipmentInterface) {
            throw new TransformationFailedException("Expected instance of " . ShipmentInterface::class);
        }

        $tree = new ArrayCollection($shipment->getItems()->toArray());
        $flat = new ArrayCollection();

        foreach ($tree as $item) {
            $this->flattenShipmentItem($item, $flat);
        }

        $shipment->setItems($flat);

        return $shipment;
    }

    /**
     * Adds item and his children to the flat collection.
     *
     * @param ShipmentItemInterface $item
     * @param ArrayCollection       $flat
     */
    private function flattenShipmentItem(ShipmentItemInterface $item, ArrayCollection $flat)
    {
        if (0 < $item->getQuantity()) {
            $flat->add($item);

            foreach ($item->getChildren() as $child) {
                $saleItem = $child->getSaleItem();

                //if ($saleItem->isPrivate()) {
                    $child->setQuantity($item->getQuantity() * $saleItem->getQuantity());
                //}

                $this->flattenShipmentItem($child, $flat);
            }
        }

        $item->clearChildren();
    }
}
