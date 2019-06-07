<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Order;

use Ekyna\Bundle\CommerceBundle\Model\InvoiceStates;
use Ekyna\Bundle\CommerceBundle\Model\PaymentStates;
use Ekyna\Bundle\CommerceBundle\Model\ShipmentStates;
use Ekyna\Component\Commerce\Invoice\Resolver\InvoicePaymentResolverInterface;
use Ekyna\Component\Commerce\Order\Export\OrderInvoiceExporter as BaseExporter;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderInvoiceRepositoryInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class OrderInvoiceExporter
 * @package Ekyna\Bundle\CommerceBundle\Service\Order
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderInvoiceExporter extends BaseExporter
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;


    /**
     * Constructor.
     *
     * @param OrderInvoiceRepositoryInterface $repository
     * @param InvoicePaymentResolverInterface $resolver
     * @param TranslatorInterface             $translator
     */
    public function __construct(
        OrderInvoiceRepositoryInterface $repository,
        InvoicePaymentResolverInterface $resolver,
        TranslatorInterface $translator
    ) {
        parent::__construct($repository, $resolver);

        $this->translator = $translator;
    }

    /**
     * @inheritDoc
     */
    protected function buildHeaders(): array
    {
        $number = $this->translator->trans('ekyna_core.field.number');
        $order = $this->translator->trans('ekyna_commerce.order.label.singular');

        return [
            'Id',
            $number,
            $this->translator->trans('ekyna_core.field.currency'),
            $this->translator->trans('ekyna_commerce.sale.field.ati_total'),
            $this->translator->trans('ekyna_commerce.sale.field.paid_total'),
            $this->translator->trans('ekyna_commerce.customer.balance.due_date'),
            'Id ' . $order,
            $number . ' ' . $order,
            $this->translator->trans('ekyna_core.field.company'),
            $this->translator->trans('ekyna_commerce.sale.field.payment_state'),
            $this->translator->trans('ekyna_commerce.sale.field.shipment_state'),
            $this->translator->trans('ekyna_commerce.sale.field.invoice_state'),
            $this->translator->trans('ekyna_commerce.payment_term.label.singular'),
            $this->translator->trans('ekyna_core.field.created_at'),
        ];
    }

    /**
     * @inheritDoc
     */
    protected function buildRow(OrderInvoiceInterface $invoice): ?array
    {
        if (null === $row = parent::buildRow($invoice)) {
            return null;
        }

        $row['payment_state'] = $this->translator->trans(PaymentStates::getLabel($row['payment_state']));
        $row['shipment_state'] = $this->translator->trans(ShipmentStates::getLabel($row['shipment_state']));
        $row['invoice_state'] = $this->translator->trans(InvoiceStates::getLabel($row['invoice_state']));

        return $row;
    }
}
