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
     * Constructor.
     *
     * @param SubjectHelper $subjectHelper
     */
    public function __construct(SubjectHelper $subjectHelper)
    {
        $this->subjectHelper = $subjectHelper;
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
        $result = $parentIds = [];
        $ati = $document->isAti();

        $lines = $document->getLinesByType(Model\DocumentLineTypes::TYPE_GOOD);

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

                $result[] = $this->buildRowByItem($parent, $level - 1);

                $parentIds[] = $parent->getId();
            }

            $result[] = $this->buildRowByLine($line, $level, $ati);
        }

        return $result;
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
