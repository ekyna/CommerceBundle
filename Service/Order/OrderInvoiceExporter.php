<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Order;

use Ekyna\Component\Commerce\Common\Export\RegionProvider;
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
    protected TranslatorInterface $translator;

    public function __construct(
        OrderInvoiceRepositoryInterface $repository,
        RegionProvider $regionProvider,
        TranslatorInterface $translator
    ) {
        parent::__construct($repository, $regionProvider);

        $this->translator = $translator;
    }

    protected function buildHeader(string $name): string
    {
        switch ($name) {
            case 'date':
                return $this->translator->trans('field.date', [], 'EkynaUi');
            case 'number':
                return $this->translator->trans('field.number', [], 'EkynaUi');
            case 'order_date':
                return $this->translator->trans('customer.balance.order_date', [], 'EkynaCommerce');
            case 'order_number':
                return $this->translator->trans('order.label.singular', [], 'EkynaCommerce');
            case 'voucher_number':
                return $this->translator->trans('sale.field.voucher_number', [], 'EkynaCommerce');
            case 'company':
                return $this->translator->trans('field.company', [], 'EkynaUi');
            case 'grand_total':
                return $this->translator->trans('sale.field.ati_total', [], 'EkynaCommerce');
            case 'paid_total':
                return $this->translator->trans('sale.field.paid_total', [], 'EkynaCommerce');
            case 'due_date':
                return $this->translator->trans('customer.balance.due_date', [], 'EkynaCommerce');
            case 'payment_term':
                return $this->translator->trans('payment_term.label.singular', [], 'EkynaCommerce');
            case 'payment_state':
                return $this->translator->trans('sale.field.payment_state', [], 'EkynaCommerce');
        }

        return $name;
    }
}
