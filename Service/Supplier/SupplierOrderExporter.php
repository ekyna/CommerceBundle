<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Supplier;

use Ekyna\Bundle\CommerceBundle\Model\SupplierOrderStates;
use Ekyna\Component\Commerce\Supplier\Export\SupplierOrderExporter as BaseExporter;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierOrderRepositoryInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class SupplierOrderExporter
 * @package Ekyna\Bundle\CommerceBundle\Service\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderExporter extends BaseExporter
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;


    /**
     * Constructor.
     *
     * @param SupplierOrderRepositoryInterface $repository
     * @param TranslatorInterface $translator
     */
    public function __construct(SupplierOrderRepositoryInterface $repository, TranslatorInterface $translator)
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
            $this->translator->trans('ekyna_commerce.field.status'),
            $this->translator->trans('ekyna_commerce.supplier_order.field.ordered_at'),
            $this->translator->trans('ekyna_core.field.completed_at'),
            $this->translator->trans('ekyna_commerce.supplier.label.singular'),
            $this->translator->trans('ekyna_commerce.supplier_order.field.payment_total'),
            $this->translator->trans('ekyna_commerce.supplier_order.field.payment_date'),
            $this->translator->trans('ekyna_commerce.supplier_order.field.payment_due_date'),
            $this->translator->trans('ekyna_commerce.supplier_carrier.label.singular'),
            $this->translator->trans('ekyna_commerce.supplier_order.field.forwarder_total'),
            $this->translator->trans('ekyna_commerce.supplier_order.field.forwarder_date'),
            $this->translator->trans('ekyna_commerce.supplier_order.field.forwarder_due_date'),
        ];
    }

    /**
     * @inheritDoc
     */
    protected function buildRow(SupplierOrderInterface $order)
    {
        $row = parent::buildRow($order);

        $row['state'] = $this->translator->trans(SupplierOrderStates::getLabel($row['state']));

        return $row;
    }
}
