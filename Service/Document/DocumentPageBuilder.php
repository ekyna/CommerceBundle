<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelper;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Document\Model as Document;
use Ekyna\Component\Commerce\Document\Model\DocumentTypes;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;

/**
 * Class DocumentPageBuilder
 * @package Ekyna\Bundle\CommerceBundle\Service\Document
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DocumentPageBuilder
{
    /**
     * @var SubjectHelper
     */
    protected $subjectHelper;

    /**
     * @var ShipmentSubjectCalculatorInterface
     */
    protected $shipmentCalculator;

    /**
     * @var array
     */
    protected $config;

    /**
     * [<(string) oid> => <(int) last page height>]
     *
     * @var array
     */
    private $heightCache;


    /**
     * Constructor.
     *
     * @param SubjectHelper                      $subjectHelper
     * @param ShipmentSubjectCalculatorInterface $shipmentCalculator
     * @param array                              $config
     */
    public function __construct(
        SubjectHelper $subjectHelper,
        ShipmentSubjectCalculatorInterface $shipmentCalculator,
        array $config = []
    ) {
        $this->subjectHelper = $subjectHelper;
        $this->shipmentCalculator = $shipmentCalculator;

        $this->config = array_replace([
            'row_height'      => 27,
            'row_desc_height' => 47,
            'page_height'     => 1399,
            'header_height'   => 370,
            'title_height'    => 130,
            'footer_height'   => 91,
        ], $config);
    }

    /**
     * Builds the document pages.
     *
     * @param Document\DocumentInterface $document
     *
     * @return array
     */
    public function buildDocumentPages(Document\DocumentInterface $document): array
    {
        $ati = $document->isAti();

        $lines = $document->getLinesByType(Document\DocumentLineTypes::TYPE_GOOD);

        $groups = [];
        $group = ['height' => 0, 'rows' => []];
        $totalHeight = 0;
        $parentIds = [];

        foreach ($lines as $line) {
            $item = $line->getSaleItem();
            if ($item->isPrivate()) {
                continue;
            }

            $level = 0;
            $parent = $item;
            while (null !== $parent = $parent->getParent()) {
                $level++;

                if (null !== $this->findDocumentLineByItem($lines, $parent)) {
                    continue;
                }

                if (in_array($parent->getId(), $parentIds)) {
                    continue;
                }

                //  New group
                if ($level - 1 == 0 && !empty($group['rows'])) {
                    $groups[] = $group;
                    $group = ['height' => 0, 'rows' => []];
                }

                $reference = $parent->getReference();
                if ($parent->isConfigurable() && $parent->isCompound() && !$parent->hasPrivateChildren()) {
                    $reference = null;
                }

                // Add parent row
                $group['rows'][] = $row = [
                    'level'         => $level - 1,
                    'virtual'       => true,
                    'reference'     => $reference,
                    'designation'   => $parent->getDesignation(),
                    'description'   => $parent->getDescription(),
                    'url'           => $this->subjectHelper->generatePublicUrl($parent, false),
                    'quantity'      => null,
                    'unit'          => null,
                    'gross'         => null,
                    'discount'      => null,
                    'base'          => null,
                    'taxRates'      => null,
                    'discountRates' => null,
                ];

                $rowHeight = empty($row['description']) ? $this->config['row_height']
                    : $this->config['row_desc_height'];

                $group['height'] += $rowHeight;
                $totalHeight += $rowHeight;

                $parentIds[] = $parent->getId();
            }

            //  New group
            if ($level == 0 && !empty($group['rows'])) {
                $groups[] = $group;
                $group = ['height' => 0, 'rows' => []];
            }

            // Add row
            $group['rows'][] = $row = [
                'level'         => $level,
                'virtual'       => false,
                'reference'     => $item->getReference(),
                'designation'   => $line->getDesignation(),
                'description'   => $line->getDescription(),
                'url'           => $this->subjectHelper->generatePublicUrl($line->getSaleItem(), false),
                'quantity'      => $line->getQuantity(),
                'unit'          => $line->getUnit($ati),
                'gross'         => $line->getGross($ati),
                'discount'      => $line->getDiscount($ati),
                'base'          => $line->getBase($ati),
                'taxRates'      => $line->getTaxRates(),
                'discountRates' => $line->getDiscountRates(),
            ];

            $rowHeight = empty($row['description']) ? $this->config['row_height'] : $this->config['row_desc_height'];

            $group['height'] += $rowHeight;
            $totalHeight += $rowHeight;
        }

        //  Add group if not empty
        if (!empty($group['rows'])) {
            $groups[] = $group;
        }

        $group = ['height' => 0, 'rows' => []];

        foreach ($document->getItems() as $item) {
            // Add row
            $group['rows'][] = $row = [
                'level'         => 0,
                'virtual'       => false,
                'reference'     => $item->getReference(),
                'designation'   => $item->getDesignation(),
                'description'   => $item->getDescription(),
                'url'           => null,
                'quantity'      => $item->getQuantity(),
                'unit'          => $item->getUnit($ati),
                'gross'         => $item->getGross($ati),
                'discount'      => $item->getDiscount($ati),
                'base'          => $item->getBase($ati),
                'taxRates'      => $item->getTaxRates(),
                'discountRates' => $item->getDiscountRates(),
            ];

            $rowHeight = empty($row['description']) ? $this->config['row_height'] : $this->config['row_desc_height'];

            $group['height'] += $rowHeight;
            $totalHeight += $rowHeight;
        }

        //  Add group if not empty
        if (!empty($group['rows'])) {
            $groups[] = $group;
        }

        return $this->buildPages($groups, $totalHeight, spl_object_id($document));
    }

    /**
     * Builds the shipment pages.
     *
     * @param Shipment\ShipmentInterface $shipment The document subject
     * @param string                     $type     The document type
     *
     * @return array
     */
    public function buildShipmentPages(Shipment\ShipmentInterface $shipment, string $type): array
    {
        /** @var Shipment\ShipmentItemInterface[] $lines */
        $lines = $shipment->getItems()->toArray();

        $groups = [];
        $group = ['height' => 0, 'rows' => []];
        $totalHeight = 0;
        $parentIds = [];

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

                if (in_array($parent->getId(), $parentIds)) {
                    continue;
                }

                //  New group
                if ($level - 1 == 0 && !empty($group['rows'])) {
                    $groups[] = $group;
                    $group = ['height' => 0, 'rows' => []];
                }

                // Add parent row
                $group['rows'][] = $row = [
                    'level'       => $level - 1,
                    'virtual'     => true,
                    'private'     => $parent->isPrivate(),
                    'designation' => $parent->getDesignation(),
                    'url'         => $this->subjectHelper->generatePublicUrl($parent, false),
                    'reference'   => $parent->getReference(),
                    'quantity'    => null,
                ];

                $group['height'] += $this->config['row_height'];
                $totalHeight += $this->config['row_height'];

                $parentIds[] = $parent->getId();
            }

            //  New group
            if ($level == 0 && !empty($group['rows'])) {
                $groups[] = $group;
                $group = ['height' => 0, 'rows' => []];
            }

            // Add row
            $group['rows'][] = $row = [
                'level'       => $level,
                'virtual'     => false,
                'private'     => $item->isPrivate(),
                'designation' => $item->getDesignation(),
                'url'         => $this->subjectHelper->generatePublicUrl($item, false),
                'reference'   => $item->getReference(),
                'quantity'    => $line->getQuantity(),
            ];

            $group['height'] += $this->config['row_height'];
            $totalHeight += $this->config['row_height'];
        }

        //  Add group if not empty
        if (!empty($group['rows'])) {
            $groups[] = $group;
        }

        return $this->buildPages($groups, $totalHeight, spl_object_id($shipment));
    }

    /**
     * Builds the shipment remaining pages.
     *
     * @param Shipment\ShipmentInterface $shipment
     *
     * @return array
     */
    public function buildShipmentRemainingPages(Shipment\ShipmentInterface $shipment): array
    {
        if ($shipment->isReturn()) {
            return [];
        }

        $list = $this->shipmentCalculator->buildRemainingList($shipment);

        if (empty($list->getEntries())) {
            return [];
        }

        $groups = [];
        $group = ['height' => 0, 'rows' => []];
        $totalHeight = 0;
        $parentIds = [];

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
                if ($level - 1 == 0 && !empty($group['rows'])) {
                    $groups[] = $group;
                    $group = ['height' => 0, 'rows' => []];
                }

                // Add parent row
                $group['rows'][] = $row = [
                    'level'       => $level - 1,
                    'virtual'     => true,
                    'private'     => $parent->isPrivate(),
                    'designation' => $parent->getDesignation(),
                    'url'         => $this->subjectHelper->generatePublicUrl($parent, false),
                    'reference'   => $parent->getReference(),
                    'quantity'    => null,
                ];
                $group['height'] += $this->config['row_height'];
                $totalHeight += $this->config['row_height'];

                $parentIds[] = $parent->getId();
            }

            //  New group
            if ($level == 0 && !empty($group['rows'])) {
                $groups[] = $group;
                $group = ['height' => 0, 'rows' => []];
            }

            // Add row
            $group['rows'][] = $row = [
                'level'       => $level,
                'virtual'     => false,
                'private'     => $item->isPrivate(),
                'designation' => $item->getDesignation(),
                'url'         => $this->subjectHelper->generatePublicUrl($item, false),
                'reference'   => $item->getReference(),
                'quantity'    => $entry->getQuantity(),
            ];
            $group['height'] += $this->config['row_height'];
            $totalHeight += $this->config['row_height'];
        }

        //  Add group if not empty
        if (!empty($group['rows'])) {
            $groups[] = $group;
        }

        return [
            'eda'   => $list->getEstimatedShippingDate(),
            'pages' => $this->buildPages($groups, $totalHeight, spl_object_id($shipment)),
        ];
    }

    /**
     * Builds the pages.
     *
     * @param array $groups
     * @param int   $totalHeight
     * @param int   $oid
     *
     * @return array
     */
    private function buildPages(array $groups, int $totalHeight, int $oid): array
    {
        $pages = $page = [];
        $pageHeight = 0;
        $lastPageMaxOffset = 250; // Totals and Taxes rows
        foreach ($groups as $group) {
            $max = $this->config['page_height'] - $this->config['title_height'] - $this->config['footer_height'];

            // If first page : keep space for customer addresses, etc...
            if (empty($pages)) {
                if (isset($this->heightCache[$oid])) {
                    $max -= $this->heightCache[$oid];
                } else {
                    $max -= $this->config['header_height'];
                }
            }

            if (
                // Last page needs space for totals rows
                ($totalHeight < $max && $pageHeight + $group['height'] + $lastPageMaxOffset > $max)
                || ($pageHeight + $group['height'] > $max)
            ) {
                $pages[] = $page;
                $page = [];

                $totalHeight -= $pageHeight;
                $this->heightCache[$oid] = $pageHeight;
                $pageHeight = 0;
            }

            $pageHeight += $group['height'];
            foreach ($group['rows'] as $row) {
                $page[] = $row;
            }
        }

        if (!empty($page)) {
            $pages[] = $page;
            $this->heightCache[$oid] = $pageHeight;
        }

        return $pages;
    }

    /**
     * Finds the document line matching the given sale item.
     *
     * @param Document\DocumentLineInterface[] $lines
     * @param SaleItemInterface                $item
     *
     * @return Document\DocumentLineInterface|null
     */
    private function findDocumentLineByItem(array $lines, SaleItemInterface $item): ?Document\DocumentLineInterface
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
     * @param Shipment\ShipmentItemInterface[] $lines
     * @param SaleItemInterface                $item
     *
     * @return Shipment\ShipmentItemInterface|null
     */
    private function findShipmentLineByItem(array $lines, SaleItemInterface $item): ?Shipment\ShipmentItemInterface
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
     * @param Shipment\RemainingList $list
     * @param SaleItemInterface      $item
     *
     * @return Shipment\RemainingEntry|null
     */
    private function findListEntryByItem(
        Shipment\RemainingList $list,
        SaleItemInterface $item
    ): ?Shipment\RemainingEntry {
        foreach ($list->getEntries() as $entry) {
            if ($entry->getSaleItem() === $item) {
                return $entry;
            }
        }

        return null;
    }
}
