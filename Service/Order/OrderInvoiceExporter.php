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
    protected function buildHeaders(): array
    {
        $number = $this->translator->trans('ekyna_core.field.number');

        return [
            $this->translator->trans('ekyna_core.field.date'),
            $number,
            $this->translator->trans('ekyna_commerce.customer.balance.order_date'),
            $this->translator->trans('ekyna_commerce.order.label.singular') . ' ' . $number,
            $this->translator->trans('ekyna_commerce.sale.field.voucher_number'),
            $this->translator->trans('ekyna_core.field.company'),
            $this->translator->trans('ekyna_commerce.sale.field.ati_total'),
            $this->translator->trans('ekyna_commerce.sale.field.paid_total'),
            $this->translator->trans('ekyna_core.field.currency'),
            $this->translator->trans('ekyna_commerce.customer.balance.due_date'),
            $this->translator->trans('ekyna_commerce.payment_term.label.singular'),
        ];
    }
}
