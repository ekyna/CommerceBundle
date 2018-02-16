<?php

namespace Ekyna\Bundle\CommerceBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentLineTypes;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceLineInterface;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class InvoiceLinesDataTransformer
 * @package Ekyna\Bundle\CommerceBundle\Form\DataTransformer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceLinesDataTransformer implements DataTransformerInterface
{
    /**
     * @var InvoiceInterface
     */
    private $invoice;


    /**
     * Constructor.
     *
     * @param InvoiceInterface $invoice
     */
    public function __construct(InvoiceInterface $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Transforms the flat invoice lines collection into a tree invoice lines collection.
     *
     * @param Collection|InvoiceLineInterface[] $flat
     *
     * @return Collection
     */
    public function transform($flat)
    {
        $sale = $this->invoice->getSale();

        $tree = new ArrayCollection();

        // Move goods lines from flat to tree for each sale items
        foreach ($sale->getItems() as $saleItem) {
            $this->buildTreeInvoiceLine($saleItem, $flat, $tree);
        }
        // Move discount lines at the end of the tree
        foreach ($this->invoice->getLinesByType(DocumentLineTypes::TYPE_DISCOUNT) as $line) {
            $tree->add($line);
        }
        // Move shipment lines at the end of the tree
        foreach ($this->invoice->getLinesByType(DocumentLineTypes::TYPE_SHIPMENT) as $line) {
            $tree->add($line);
        }

        return $tree;
    }

    /**
     * Transforms the tree invoice lines collection into a flat invoice lines collection.
     *
     * @param Collection|InvoiceLineInterface[] $tree
     *
     * @return Collection
     */
    public function reverseTransform($tree)
    {
        $flat = new ArrayCollection();

        foreach ($tree as $item) {
            $this->flattenInvoiceLine($item, $flat);
        }

        return $flat;
    }

    /**
     * Builds the tree invoice line.
     *
     * @param SaleItemInterface $saleItem
     * @param Collection        $flat
     * @param Collection        $parent
     */
    private function buildTreeInvoiceLine(SaleItemInterface $saleItem, Collection $flat, Collection $parent)
    {
        $invoiceLine = null;

        // Skip compound sale items with only public children
        if (!($saleItem->isCompound() && !$saleItem->hasPrivateChildren())) {
            // Look for an existing invoice line
            /** @var InvoiceLineInterface $line */
            foreach ($flat as $line) {
                if ($line->getSaleItem() === $saleItem) {
                    $invoiceLine = $line->clearChildren();
                    break;
                }
            }
        }

        $addTo = null !== $invoiceLine ? $invoiceLine->getChildren() : $parent;

        foreach ($saleItem->getChildren() as $childSaleItem) {
            $this->buildTreeInvoiceLine($childSaleItem, $flat, $addTo);
        }

        if (null !== $invoiceLine) {
            $parent->add($invoiceLine);
        }
    }

    /**
     * Adds item and his children to the flat collection.
     *
     * @param InvoiceLineInterface $line
     * @param ArrayCollection      $flat
     */
    private function flattenInvoiceLine(InvoiceLineInterface $line, ArrayCollection $flat)
    {
        if (0 < $line->getQuantity()) {
            $flat->add($line);
        }

        if ($line->getType() === DocumentLineTypes::TYPE_GOOD) {
            $override = $line->getSaleItem()->isCompound() && $line->getSaleItem()->hasPrivateChildren();

            foreach ($line->getChildren() as $child) {
                if ($override) {
                    $child->setQuantity($line->getQuantity() * $child->getSaleItem()->getQuantity());
                }

                $this->flattenInvoiceLine($child, $flat);
            }
        }

        $line->clearChildren();
    }
}
