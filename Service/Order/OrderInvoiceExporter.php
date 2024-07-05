<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Order;

use Ekyna\Component\Commerce\Order\Export\OrderInvoiceExporter as BaseExporter;
use Ekyna\Component\Commerce\Order\Repository\OrderInvoiceRepositoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class OrderInvoiceExporter
 * @package Ekyna\Bundle\CommerceBundle\Service\Order
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderInvoiceExporter extends BaseExporter
{
    public function __construct(
        OrderInvoiceRepositoryInterface        $repository,
        protected readonly TranslatorInterface $translator,
    ) {
        parent::__construct($repository);
    }

    protected function buildHeader(string $name): string
    {
        return match ($name) {
            'date'           => $this->translator->trans('field.date', [], 'EkynaUi'),
            'number'         => $this->translator->trans('field.number', [], 'EkynaUi'),
            'order_date'     => $this->translator->trans('customer.balance.order_date', [], 'EkynaCommerce'),
            'order_number'   => $this->translator->trans('order.label.singular', [], 'EkynaCommerce'),
            'voucher_number' => $this->translator->trans('sale.field.voucher_number', [], 'EkynaCommerce'),
            'company'        => $this->translator->trans('field.company', [], 'EkynaUi'),
            'grand_total'    => $this->translator->trans('sale.field.ati_total', [], 'EkynaCommerce'),
            'paid_total'     => $this->translator->trans('sale.field.paid_total', [], 'EkynaCommerce'),
            'due_date'       => $this->translator->trans('customer.balance.due_date', [], 'EkynaCommerce'),
            'payment_term'   => $this->translator->trans('payment_term.label.singular', [], 'EkynaCommerce'),
            'payment_state'  => $this->translator->trans('sale.field.payment_state', [], 'EkynaCommerce'),
            default          => $name,
        };
    }
}
