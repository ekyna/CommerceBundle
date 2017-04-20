<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Order;

use Ekyna\Bundle\CommerceBundle\Model\InvoiceStates;
use Ekyna\Bundle\CommerceBundle\Model\PaymentStates;
use Ekyna\Bundle\CommerceBundle\Model\ShipmentStates;
use Ekyna\Component\Commerce\Order\Export\OrderListExporter as BaseExporter;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class OrdersExporter
 * @package Ekyna\Bundle\CommerceBundle\Service\Order
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderListExporter extends BaseExporter
{
    protected TranslatorInterface $translator;

    public function __construct(OrderRepositoryInterface $repository, TranslatorInterface $translator)
    {
        parent::__construct($repository);

        $this->translator = $translator;
    }

    protected function buildHeader(string $name): string
    {
        switch ($name) {
            case 'number':
                return $this->translator->trans('field.number', [], 'EkynaUi');
            case 'voucher_number':
                return $this->translator->trans('sale.field.voucher_number', [], 'EkynaCommerce');
            case 'company':
                return $this->translator->trans('field.company', [], 'EkynaUi');
            case 'payment_state':
                return $this->translator->trans('sale.field.payment_state', [], 'EkynaCommerce');
            case 'shipment_state':
                return $this->translator->trans('sale.field.shipment_state', [], 'EkynaCommerce');
            case 'invoice_state':
                return $this->translator->trans('sale.field.invoice_state', [], 'EkynaCommerce');
            case 'payment_term':
                return $this->translator->trans('payment_term.label.singular', [], 'EkynaCommerce');
            case 'grand_total':
                return $this->translator->trans('sale.field.ati_total', [], 'EkynaCommerce');
            case 'paid_total':
                return $this->translator->trans('sale.field.paid_total', [], 'EkynaCommerce');
            case 'invoice_total':
                return $this->translator->trans('sale.field.invoice_total', [], 'EkynaCommerce');
            case 'due_amount':
                return $this->translator->trans('dashboard.export.field.due_amount', [], 'EkynaCommerce');
            case 'outstanding_expired':
                return $this->translator->trans('sale.field.outstanding_expired', [], 'EkynaCommerce');
            case 'outstanding_date':
                return $this->translator->trans('sale.field.outstanding_date', [], 'EkynaCommerce');
            case 'created_at':
                return $this->translator->trans('field.created_at', [], 'EkynaUi');
        }

        return $name;
    }

    protected function transform(string $name, string $value): string
    {
        switch ($name) {
            case 'payment_state':
                return PaymentStates::getLabel($value)->trans($this->translator);
            case 'shipment_state':
                return ShipmentStates::getLabel($value)->trans($this->translator);
            case 'invoice_state':
                return InvoiceStates::getLabel($value)->trans($this->translator);
        }

        return $value;
    }
}
