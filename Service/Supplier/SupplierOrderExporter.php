<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Supplier;

use Ekyna\Bundle\CommerceBundle\Model\SupplierOrderStates;
use Ekyna\Component\Commerce\Supplier\Export\SupplierOrderExporter as BaseExporter;
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
     * @param TranslatorInterface              $translator
     */
    public function __construct(SupplierOrderRepositoryInterface $repository, TranslatorInterface $translator)
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
            case 'state':
                return $this->translator->trans('ekyna_commerce.field.status');
            case 'ordered_at':
                return $this->translator->trans('ekyna_commerce.supplier_order.field.ordered_at');
            case 'completed_at':
                return $this->translator->trans('ekyna_core.field.completed_at');
            case 'supplier':
                return $this->translator->trans('ekyna_commerce.supplier.label.singular');
            case 'payment_total':
                return $this->translator->trans('ekyna_commerce.supplier_order.field.payment_total');
            case 'payment_date' :
                return $this->translator->trans('ekyna_commerce.supplier_order.field.payment_date');
            case 'payment_due_date':
                return $this->translator->trans('ekyna_commerce.supplier_order.field.payment_due_date');
            case 'carrier':
                return $this->translator->trans('ekyna_commerce.supplier_carrier.label.singular');
            case 'forwarder_total':
                return $this->translator->trans('ekyna_commerce.supplier_order.field.forwarder_total');
            case 'forwarder_date':
                return $this->translator->trans('ekyna_commerce.supplier_order.field.forwarder_date');
            case 'forwarder_due_date':
                return $this->translator->trans('ekyna_commerce.supplier_order.field.forwarder_due_date');
        };

        return $name;
    }

    /**
     * @inheritDoc
     */
    protected function transform(string $name, string $value): string
    {
        if ($name === 'state') {
            return $this->translator->trans(SupplierOrderStates::getLabel($value));
        }

        return $value;
    }
}
