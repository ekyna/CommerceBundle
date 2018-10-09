<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelper;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Document\Model;

/**
 * Class DocumentRowsBuilder
 * @package Ekyna\Bundle\CommerceBundle\Service\Document
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DocumentRowsBuilder
{
    /**
     * @var SubjectHelper
     */
    protected $subjectHelper;

    /**
     * @var array
     */
    protected $config;


    /**
     * Constructor.
     *
     * @param SubjectHelper $subjectHelper
     * @param array         $config
     */
    public function __construct(SubjectHelper $subjectHelper, array $config = [])
    {
        $this->subjectHelper = $subjectHelper;

        $this->config = array_replace([
            'row_height'      => 20,
            'row_desc_height' => 31,
            'page_height'     => 766,
            'header_height'   => 286,
            'footer_height'   => 150,
        ], $config);
    }

    /**
     * Builds the document goods rows.
     *
     * @param Model\DocumentInterface $document
     *
     * @return array
     */
    public function buildGoodRows(Model\DocumentInterface $document)
    {
        $ati = $document->isAti();

        $lines = $document->getLinesByType(Model\DocumentLineTypes::TYPE_GOOD);

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

                if (null !== $this->findLineByItem($lines, $parent)) {
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
                $group['rows'][] = $row = $this->buildRowByItem($parent, $level - 1);
                $rowHeight = empty($row['description']) ? $this->config['row_height'] : $this->config['row_desc_height'];
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
            $group['rows'][] = $row = $this->buildRowByLine($line, $level, $ati);
            $rowHeight = empty($row['description']) ? $this->config['row_height'] : $this->config['row_desc_height'];
            $group['height'] += $rowHeight;
            $totalHeight += $rowHeight;
        }

        $pages = $page = [];
        $pageHeight = 0;
        foreach ($groups as $group) {
            $max = $this->config['page_height'];

            // If first page : keep space for customer addresses, etc...
            if (empty($pages)) {
                $max -= $this->config['header_height'];
            }

            if (
                ($totalHeight < $max && $totalHeight + $this->config['footer_height'] > $max) // Last page needs space for totals rows
                || ($pageHeight + $group['height'] > $max)
            ) {
                $pages[] = $page;
                $page = [];

                $totalHeight -= $pageHeight;
                $pageHeight = 0;
            }

            $pageHeight += $group['height'];
            foreach ($group['rows'] as $row) {
                $page[] = $row;
            }
        }

        if (!empty($page)) {
            $pages[] = $page;
        }

        return $pages;
    }

    /**
     * Builds the row by document line.
     *
     * @param Model\DocumentLineInterface $line
     * @param int                         $level The row level
     * @param bool                        $ati   Whether to display ATI prices
     *
     * @return array
     */
    private function buildRowByLine(Model\DocumentLineInterface $line, int $level, bool $ati = false)
    {
        return [
            'level'         => $level,
            'virtual'       => false,
            'reference'     => $line->getReference(),
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
    }

    /**
     * Builds the row by sale item.
     *
     * @param SaleItemInterface $item
     * @param int               $level The row level
     *
     * @return array
     */
    private function buildRowByItem(SaleItemInterface $item, int $level)
    {
        return [
            'level'         => $level,
            'virtual'       => true,
            'reference'     => $item->getReference(),
            'designation'   => $item->getDesignation(),
            'description'   => $item->getDescription(),
            'url'           => $this->subjectHelper->generatePublicUrl($item, false),
            'quantity'      => null,
            'unit'          => null,
            'gross'         => null,
            'discount'      => null,
            'base'          => null,
            'taxRates'      => null,
            'discountRates' => null,
        ];
    }

    /**
     * Finds the line matching the given sale item.
     *
     * @param Model\DocumentLineInterface[] $lines
     * @param SaleItemInterface             $item
     *
     * @return Model\DocumentLineInterface|null
     */
    private function findLineByItem(array $lines, SaleItemInterface $item)
    {
        foreach ($lines as $line) {
            if ($line->getSaleItem() === $item) {
                return $line;
            }
        }

        return null;
    }
}
