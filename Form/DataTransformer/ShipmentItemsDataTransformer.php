<?php

namespace Ekyna\Bundle\CommerceBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class ShipmentItemsDataTransformer
 * @package Ekyna\Bundle\CommerceBundle\Form\DataTransformer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentItemsDataTransformer implements DataTransformerInterface
{
    /**
     * @var ShipmentInterface
     */
    private $shipment;


    /**
     * Constructor.
     *
     * @param ShipmentInterface $shipment
     */
    public function __construct(ShipmentInterface $shipment)
    {
        $this->shipment = $shipment;
    }

    /**
     * Transforms the flat shipment items collection into a tree shipment items collection.
     *
     * @param Collection|ShipmentItemInterface[] $flat
     *
     * @return Collection
     */
    public function transform($flat)
    {
        $sale = $this->shipment->getSale();

        $tree = new ArrayCollection();

        // Move shipment items from flat to tree for each sale items
        foreach ($sale->getItems() as $saleItem) {
            $this->buildTreeShipmentItem($saleItem, $flat, $tree);
        }

        return $tree;
    }

    /**
     * Transforms the tree shipment items collection into a flat shipment items collection.
     *
     * @param Collection|ShipmentItemInterface[] $tree
     *
     * @return Collection
     */
    public function reverseTransform($tree)
    {
        $flat = new ArrayCollection();

        foreach ($tree as $item) {
            $this->flattenShipmentItem($item, $flat);
        }

        return $flat;
    }

    /**
     * Builds the tree shipment item.
     *
     * @param SaleItemInterface $saleItem
     * @param Collection        $flat
     * @param Collection        $parent
     */
    private function buildTreeShipmentItem(SaleItemInterface $saleItem, Collection $flat, Collection $parent)
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
     * Adds item and his children to the flat collection.
     *
     * @param ShipmentItemInterface $item
     * @param Collection            $flat
     */
    private function flattenShipmentItem(ShipmentItemInterface $item, Collection $flat)
    {
        if (0 < $item->getQuantity()) {
            $flat->add($item);
        }

        $override = $item->getSaleItem()->isCompound() && $item->getSaleItem()->hasPrivateChildren();

        foreach ($item->getChildren() as $child) {
            if ($override) {
                $child->setQuantity($item->getQuantity() * $child->getSaleItem()->getQuantity());
            }

            $this->flattenShipmentItem($child, $flat);
        }

        $item->clearChildren();
    }
}
