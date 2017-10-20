<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;

/**
 * Class InvoiceRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Document
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceRenderer extends AbstractRenderer
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
     * @inheritDoc
     */
    public function getLastModified()
    {
        return $this->invoice->getUpdatedAt();
    }

    /**
     * @inheritdoc
     */
    public function getFilename()
    {
        return $this->invoice->getNumber();
    }

    /**
     * @inheritdoc
     */
    protected function getContent()
    {
        return $this->renderView('EkynaCommerceBundle:Document:invoice.html.twig', [
            'logo_path' => $this->logoPath,
            'document'  => $this->invoice,
        ]);
    }
}
