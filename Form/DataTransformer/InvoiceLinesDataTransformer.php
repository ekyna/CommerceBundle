<?php

namespace Ekyna\Bundle\CommerceBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentLineTypes;
use Ekyna\Component\Commerce\Invoice\Builder\InvoiceBuilderInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceLineInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class InvoiceLinesDataTransformer
 * @package Ekyna\Bundle\CommerceBundle\Form\DataTransformer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceLinesDataTransformer implements DataTransformerInterface
{
    /**
     * @var InvoiceBuilderInterface
     */
    private $builder;


    /**
     * Constructor.
     *
     * @param InvoiceBuilderInterface $builder
     */
    public function __construct(InvoiceBuilderInterface $builder)
    {
        $this->builder = $builder;
    }

    /**
     * Transforms the flat invoice items collection into a tree invoice items collection.
     *
     * @param InvoiceInterface $invoice
     *
     * @return InvoiceInterface
     */
    public function transform($invoice)
    {
        if (!$invoice instanceof InvoiceInterface) {
            throw new TransformationFailedException("Expected instance of " . InvoiceInterface::class);
        }

        $this->builder->build($invoice);

        $flat = new ArrayCollection($invoice->getLinesByType(DocumentLineTypes::TYPE_GOOD));
        $tree = new ArrayCollection();

        // Move goods lines from flat to tree for each sale items
        foreach ($invoice->getSale()->getItems() as $saleItem) {
            $this->buildTreeInvoiceLine($saleItem, $flat, $tree);
        }
        // Move discount lines at the end of the tree
        foreach ($invoice->getLinesByType(DocumentLineTypes::TYPE_DISCOUNT) as $line) {
            $tree->add($line);
        }
        // Move shipment lines at the end of the tree
        foreach ($invoice->getLinesByType(DocumentLineTypes::TYPE_SHIPMENT) as $line) {
            $tree->add($line);
        }

        // Replaces lines
        $invoice->setLines($tree);

        return $invoice;
    }

    /**
     * Builds the tree invoice line.
     *
     * @param SaleItemInterface $saleItem
     * @param Collection        $flat
     * @param Collection        $parent
     */
    private function buildTreeInvoiceLine(
        SaleItemInterface $saleItem,
        Collection $flat,
        Collection $parent
    ) {
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
     * Transforms the tree invoice items collection into a flat invoice items collection.
     *
     * @param InvoiceInterface $invoice
     *
     * @return InvoiceInterface
     */
    public function reverseTransform($invoice)
    {
        if (!$invoice instanceof InvoiceInterface) {
            throw new TransformationFailedException("Expected instance of " . InvoiceInterface::class);
        }

        $tree = $invoice->getLines();
        $flat = new ArrayCollection();

        foreach ($tree as $item) {
            $this->flattenInvoiceLine($item, $flat);
        }

        $invoice->setLines($flat);

        return $invoice;
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

            foreach ($line->getChildren() as $child) {
                $saleItem = $child->getSaleItem();
                if ($saleItem->isPrivate()) {
                    $child->setQuantity($line->getQuantity() * $saleItem->getQuantity());
                }

                $this->flattenInvoiceLine($child, $flat);
            }
        }

        $line->clearChildren();
    }
}
