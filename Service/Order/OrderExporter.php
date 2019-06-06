<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Order;

use Ekyna\Bundle\CommerceBundle\Model\InvoiceStates;
use Ekyna\Bundle\CommerceBundle\Model\PaymentStates;
use Ekyna\Bundle\CommerceBundle\Model\ShipmentStates;
use Ekyna\Component\Commerce\Order\Export\OrderExporter as BaseExporter;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class OrderExporter
 * @package Ekyna\Bundle\CommerceBundle\Service\Order
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderExporter extends BaseExporter
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
    protected function buildHeaders()
    {
        return [
            'id',
            $this->translator->trans('ekyna_core.field.number'),
            $this->translator->trans('ekyna_core.field.company'),
            $this->translator->trans('ekyna_commerce.sale.field.payment_state'),
            $this->translator->trans('ekyna_commerce.sale.field.shipment_state'),
            $this->translator->trans('ekyna_commerce.sale.field.invoice_state'),
            $this->translator->trans('ekyna_commerce.payment_term.label.singular'),
            $this->translator->trans('ekyna_commerce.dashboard.export.field.due_amount'),
            $this->translator->trans('ekyna_commerce.sale.field.outstanding_expired'),
            $this->translator->trans('ekyna_commerce.sale.field.outstanding_date'),
            $this->translator->trans('ekyna_core.field.created_at'),
        ];
    }

    /**
     * @inheritDoc
     */
    protected function buildRow(OrderInterface $order)
    {
        $row = parent::buildRow($order);

        $row['payment_state'] = $this->translator->trans(PaymentStates::getLabel($row['payment_state']));
        $row['shipment_state'] = $this->translator->trans(ShipmentStates::getLabel($row['shipment_state']));
        $row['invoice_state'] = $this->translator->trans(InvoiceStates::getLabel($row['invoice_state']));

        return $row;
    }
}
