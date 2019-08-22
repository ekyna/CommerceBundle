<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Order;

use Ekyna\Component\Commerce\Order\Export\OrderInvoiceExporter as BaseExporter;
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
     * @param TranslatorInterface             $translator
     */
    public function __construct(OrderInvoiceRepositoryInterface $repository, TranslatorInterface $translator)
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
            case 'date':
                return $this->translator->trans('ekyna_core.field.date');
            case 'number':
                return $this->translator->trans('ekyna_core.field.number');
            case 'order_date':
                return $this->translator->trans('ekyna_commerce.customer.balance.order_date');
            case 'order_number':
                return $this->translator->trans('ekyna_commerce.order.label.singular');
            case 'voucher_number':
                return $this->translator->trans('ekyna_commerce.sale.field.voucher_number');
            case 'company':
                return $this->translator->trans('ekyna_core.field.company');
            case 'grand_total':
                return $this->translator->trans('ekyna_commerce.sale.field.ati_total');
            case 'paid_total':
                return $this->translator->trans('ekyna_commerce.sale.field.paid_total');
            case 'due_date':
                return $this->translator->trans('ekyna_commerce.customer.balance.due_date');
            case 'payment_term':
                return $this->translator->trans('ekyna_commerce.payment_term.label.singular');
            case 'payment_state':
                return $this->translator->trans('ekyna_commerce.sale.field.payment_state');
        }

        return $name;
    }
}
