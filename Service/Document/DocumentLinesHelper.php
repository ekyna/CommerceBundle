<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelper;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentLineInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentLineTypes;
use Ekyna\Component\Commerce\Document\Model\DocumentTypes;
use Ekyna\Component\Commerce\Document\Util\DocumentUtil;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Model\RemainingEntry;
use Ekyna\Component\Commerce\Shipment\Model\RemainingList;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface;

use function array_replace;
use function in_array;

/**
 * Class DocumentLinesHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Document
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DocumentLinesHelper
{
    protected array $config;

    public function __construct(
        protected readonly SubjectHelper                      $subjectHelper,
        protected readonly ShipmentSubjectCalculatorInterface $shipmentCalculator,
        array                                                 $config = []
    ) {
        $this->config = array_replace([
            'shipment_remaining_date' => true,
        ], $config);
    }

    public function buildDocumentLines(DocumentInterface $document): array
    {
        $groups = $group = $parentIds = [];
        $ati = $document->isAti();

        $lines = $document->getLinesByType(DocumentLineTypes::TYPE_GOOD);

        foreach ($lines as $line) {
            // Skip private lines if they belong to a public line shown in this document.
            $item = $line->getSaleItem();
            if ($item->isPrivate() && DocumentUtil::hasPublicParent($document, $item)) {
                continue;
            }

            // Add parents lines if they are not yet shown in this document.
            $level = 0;
            $parent = $item;
            while (null !== $parent = $parent->getParent()) {
                $level++;

                // Do not add twice
                if (null !== $this->findDocumentLineByItem($lines, $parent)) {
                    continue;
                }
                if (in_array($parent->getId(), $parentIds, true)) {
                    continue;
                }

                //  New group
                if (!empty($group) && (0 === $level - 1)) {
                    $groups[] = $group;
                    $group = [];
                }

                $parentIds[] = $parent->getId();

                $reference = $parent->getReference();
                if ($parent->isConfigurable() && $parent->isCompound() && !$parent->hasPrivateChildren()) {
                    $reference = null;
                }

                // Add parent line row
                $group[] = [
                    'level'         => $level - 1,
                    'virtual'       => true,
                    'reference'     => $reference,
                    'designation'   => $parent->getDesignation(),
                    'description'   => null,
                    'included'      => null,
                    'url'           => $this->subjectHelper->generatePublicUrl($parent, false),
                    'quantity'      => null,
                    'unit'          => null,
                    'gross'         => null,
                    'discount'      => null,
                    'base'          => null,
                    'taxRates'      => null,
                    'discountRates' => null,
                ];
            }

            //  New group
            if (!empty($group) && (0 === $level)) {
                $groups[] = $group;
                $group = [];
            }

            // Add line row
            $group[] = [
                'level'         => $level,
                'virtual'       => false,
                'reference'     => $line->getReference(),
                'designation'   => $line->getDesignation(),
                'description'   => $line->getDescription(),
                'included'      => $line->getIncluded(),
                'url'           => $this->subjectHelper->generatePublicUrl($line->getSaleItem(), false),
                'quantity'      => $line->getQuantity(),
                'unit'          => $line->getUnit($ati),
                'gross'         => $line->getGross($ati),
                'discount'      => $line->getDiscount($ati),
                'base'          => $line->getBase($ati),
                'taxRates'      => $line->getTaxRates(),
                'discountRates' => $line->getDiscountRates(),
            ];
        }

        //  Add the group if it is not empty
        if (!empty($group)) {
            $groups[] = $group;
        }

        // Add item rows
        foreach ($document->getItems() as $item) {
            $groups[] = [
                [
                    'level'         => 0,
                    'virtual'       => false,
                    'reference'     => $item->getReference(),
                    'designation'   => $item->getDesignation(),
                    'description'   => $item->getDescription(),
                    'included'      => $item->getIncluded(),
                    'url'           => null,
                    'quantity'      => $item->getQuantity(),
                    'unit'          => $item->getUnit($ati),
                    'gross'         => $item->getGross($ati),
                    'discount'      => $item->getDiscount($ati),
                    'base'          => $item->getBase($ati),
                    'taxRates'      => $item->getTaxRates(),
                    'discountRates' => $item->getDiscountRates(),
                ],
            ];
        }

        return $groups;
    }

    public function buildShipmentLines(ShipmentInterface $shipment, string $type): array
    {
        $groups = $group = $parentIds = [];
        /** @var ShipmentItemInterface[] $lines */
        $lines = $shipment->getItems()->toArray();

        foreach ($lines as $line) {
            $item = $line->getSaleItem();
            if ($item->isPrivate() && DocumentTypes::TYPE_SHIPMENT_BILL === $type) {
                continue;
            }

            $level = 0;
            $parent = $item;
            while (null !== $parent = $parent->getParent()) {
                $level++;

                if (null !== $this->findShipmentLineByItem($lines, $parent)) {
                    continue;
                }

                if (in_array($parent->getId(), $parentIds, true)) {
                    continue;
                }

                //  New group
                if (!empty($group) && (0 === $level - 1)) {
                    $groups[] = $group;
                    $group = [];
                }

                // Add parent row
                $group[] = [
                    'level'       => $level - 1,
                    'virtual'     => true,
                    'private'     => $parent->isPrivate(),
                    'designation' => $parent->getDesignation(),
                    'url'         => $this->subjectHelper->generatePublicUrl($parent, false),
                    'reference'   => $parent->getReference(),
                    'quantity'    => null,
                ];

                $parentIds[] = $parent->getId();
            }

            //  New group
            if (!empty($group) && (0 === $level)) {
                $groups[] = $group;
                $group = [];
            }

            // Add row
            $group[] = [
                'level'       => $level,
                'virtual'     => false,
                'private'     => $item->isPrivate(),
                'designation' => $item->getDesignation(),
                'url'         => $this->subjectHelper->generatePublicUrl($item, false),
                'reference'   => $item->getReference(),
                'quantity'    => $line->getQuantity(),
            ];
        }

        if (!empty($group)) {
            $groups[] = $group;
        }

        return $groups;
    }

    public function buildShipmentRemainingLines(ShipmentInterface $shipment): array
    {
        if ($shipment->isReturn()) {
            return [];
        }

        $list = $this->shipmentCalculator->buildRemainingList($shipment);

        if (empty($list->getEntries())) {
            return [];
        }

        $groups = $group = $parentIds = [];

        foreach ($list->getEntries() as $entry) {
            $item = $entry->getSaleItem();
            if ($item->isPrivate()) {
                continue;
            }

            $level = 0;
            $parent = $item;
            while (null !== $parent = $parent->getParent()) {
                $level++;

                if (null !== $this->findListEntryByItem($list, $parent)) {
                    continue;
                }

                if (in_array($parent->getId(), $parentIds)) {
                    continue;
                }

                //  New group
                if (!empty($group) && (0 === $level - 1)) {
                    $groups[] = $group;
                    $group = [];
                }

                // Add parent row
                $group[] = [
                    'level'       => $level - 1,
                    'virtual'     => true,
                    'private'     => $parent->isPrivate(),
                    'designation' => $parent->getDesignation(),
                    'url'         => $this->subjectHelper->generatePublicUrl($parent, false),
                    'reference'   => $parent->getReference(),
                    'quantity'    => null,
                ];

                $parentIds[] = $parent->getId();
            }

            //  New group
            if (!empty($group) && (0 === $level)) {
                $groups[] = $group;
                $group = [];
            }

            // Add row
            $group[] = [
                'level'       => $level,
                'virtual'     => false,
                'private'     => $item->isPrivate(),
                'designation' => $item->getDesignation(),
                'url'         => $this->subjectHelper->generatePublicUrl($item, false),
                'reference'   => $item->getReference(),
                'quantity'    => $entry->getQuantity(),
            ];
        }

        //  Add group if not empty
        if (!empty($group)) {
            $groups[] = $group;
        }

        return [
            'eda'    => $this->config['shipment_remaining_date'] ? $list->getEstimatedShippingDate() : null,
            'groups' => $groups,
        ];
    }

    /**
     * Finds the document line matching the given sale item.
     *
     * @param array<int, DocumentLineInterface> $lines
     * @param SaleItemInterface                 $item
     *
     * @return DocumentLineInterface|null
     */
    private function findDocumentLineByItem(array $lines, SaleItemInterface $item): ?DocumentLineInterface
    {
        foreach ($lines as $line) {
            if ($line->getSaleItem() === $item) {
                return $line;
            }
        }

        return null;
    }

    /**
     * Finds the shipment item matching the given sale item.
     *
     * @param ShipmentItemInterface[] $lines
     * @param SaleItemInterface       $item
     *
     * @return ShipmentItemInterface|null
     */
    private function findShipmentLineByItem(array $lines, SaleItemInterface $item): ?ShipmentItemInterface
    {
        foreach ($lines as $line) {
            if ($line->getSaleItem() === $item) {
                return $line;
            }
        }

        return null;
    }

    /**
     * Finds the shipment remaining entry matching the given sale item.
     *
     * @param RemainingList     $list
     * @param SaleItemInterface $item
     *
     * @return RemainingEntry|null
     */
    private function findListEntryByItem(
        RemainingList     $list,
        SaleItemInterface $item
    ): ?RemainingEntry {
        foreach ($list->getEntries() as $entry) {
            if ($entry->getSaleItem() === $item) {
                return $entry;
            }
        }

        return null;
    }
}
