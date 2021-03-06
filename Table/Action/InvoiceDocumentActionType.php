<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Action;

use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Table\Action\AbstractActionType;
use Ekyna\Component\Table\Action\ActionInterface;
use Ekyna\Component\Table\Source\RowInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class InvoiceDocumentActionType
 * @package Ekyna\Bundle\CommerceBundle\Table\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceDocumentActionType extends AbstractActionType
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;


    /**
     * Constructor.
     *
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @inheritDoc
     */
    public function execute(ActionInterface $action, array $options)
    {
        $table = $action->getTable();

        // The selected row's
        $rows = $table->getSourceAdapter()->getSelection(
            $table->getContext()
        );

        //$ids = $table->getContext()->getSelectedIdentifiers();

        $invoices = array_map(function (RowInterface $row) {
            return $row->getData();
        }, $rows);

        if (empty($invoices)) {
            return null;
        }

        if (1 === count($invoices)) {
            /** @var \Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface $invoice */
            $invoice = reset($invoices);

            return new RedirectResponse($this->urlGenerator->generate('ekyna_commerce_order_invoice_admin_render', [
                'orderId'         => $invoice->getOrder()->getId(),
                'orderInvoiceId' => $invoice->getId(),
            ]));
        }

        return new RedirectResponse($this->urlGenerator->generate('ekyna_commerce_admin_order_list_invoice_document', [
            'id' => array_map(function(InvoiceInterface $invoice) {
                return $invoice->getId();
            }, $invoices),
        ]));
    }
}
