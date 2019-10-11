<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Order;

use Ekyna\Bundle\CommerceBundle\Model\InvoiceStates;
use Ekyna\Bundle\CommerceBundle\Model\PaymentStates;
use Ekyna\Bundle\CommerceBundle\Model\ShipmentStates;
use Ekyna\Component\Commerce\Order\Export\OrderListExporter as BaseExporter;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class OrdersExporter
 * @package Ekyna\Bundle\CommerceBundle\Service\Order
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderListExporter extends BaseExporter
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;


    /**
     * Constructor.
     *
     * @param OrderRepositoryInterface $repository
     * @param TranslatorInterface      $translator
     */
    public function __construct(OrderRepositoryInterface $repository, TranslatorInterface $translator)
    {
        parent::__construct($repository);

        $this->translator = $translator;
    }

    /**
     * @inheritDoc
     */
    protected function buildHeader(string $name): string
    {
        switch ($name) {
            case 'number':
                return $this->translator->trans('ekyna_core.field.number');
            case 'voucher_number':
                return $this->translator->trans('ekyna_commerce.sale.field.voucher_number');
            case 'company':
                return $this->translator->trans('ekyna_core.field.company');
            case 'payment_state':
                return $this->translator->trans('ekyna_commerce.sale.field.payment_state');
            case 'shipment_state':
                return $this->translator->trans('ekyna_commerce.sale.field.shipment_state');
            case 'invoice_state':
                return $this->translator->trans('ekyna_commerce.sale.field.invoice_state');
            case 'payment_term':
                return $this->translator->trans('ekyna_commerce.payment_term.label.singular');
            case 'grand_total':
                return $this->translator->trans('ekyna_commerce.sale.field.ati_total');
            case 'paid_total':
                return $this->translator->trans('ekyna_commerce.sale.field.paid_total');
            case 'invoice_total':
                return $this->translator->trans('ekyna_commerce.sale.field.invoice_total');
            case 'due_amount':
                return $this->translator->trans('ekyna_commerce.dashboard.export.field.due_amount');
            case 'outstanding_expired':
                return $this->translator->trans('ekyna_commerce.sale.field.outstanding_expired');
            case 'outstanding_date':
                return $this->translator->trans('ekyna_commerce.sale.field.outstanding_date');
            case 'created_at':
                return $this->translator->trans('ekyna_core.field.created_at');
        }

        return $name;
    }

    /**
     * @inheritDoc
     */
    protected function transform(string $name, string $value): string
    {
        switch ($name) {
            case 'payment_state':
                return $this->translator->trans(PaymentStates::getLabel($value));
            case 'shipment_state':
                return $this->translator->trans(ShipmentStates::getLabel($value));
            case 'invoice_state':
                return $this->translator->trans(InvoiceStates::getLabel($value));
        }

        return $value;
    }
}
