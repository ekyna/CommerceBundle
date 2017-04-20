<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Supplier;

use Ekyna\Bundle\CommerceBundle\Model\SupplierOrderStates;
use Ekyna\Component\Commerce\Supplier\Export\SupplierOrderExporter as BaseExporter;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierOrderRepositoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class SupplierOrderExporter
 * @package Ekyna\Bundle\CommerceBundle\Service\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderExporter extends BaseExporter
{
    protected TranslatorInterface $translator;

    public function __construct(SupplierOrderRepositoryInterface $repository, TranslatorInterface $translator)
    {
        parent::__construct($repository);

        $this->translator = $translator;
    }

    protected function buildHeader(string $name): string
    {
        switch ($name) {
            case 'number':
                return $this->translator->trans('field.number', [], 'EkynaUi');
            case 'state':
                return $this->translator->trans('field.status', [], 'EkynaCommerce');
            case 'ordered_at':
                return $this->translator->trans('supplier_order.field.ordered_at', [], 'EkynaCommerce');
            case 'completed_at':
                return $this->translator->trans('field.completed_at', [], 'EkynaUi');
            case 'supplier':
                return $this->translator->trans('supplier.label.singular', [], 'EkynaCommerce');
            case 'payment_total':
                return $this->translator->trans('supplier_order.field.payment_total', [], 'EkynaCommerce');
            case 'payment_date' :
                return $this->translator->trans('supplier_order.field.payment_date', [], 'EkynaCommerce');
            case 'payment_due_date':
                return $this->translator->trans('supplier_order.field.payment_due_date', [], 'EkynaCommerce');
            case 'carrier':
                return $this->translator->trans('supplier_carrier.label.singular', [], 'EkynaCommerce');
            case 'forwarder_total':
                return $this->translator->trans('supplier_order.field.forwarder_total', [], 'EkynaCommerce');
            case 'forwarder_date':
                return $this->translator->trans('supplier_order.field.forwarder_date', [], 'EkynaCommerce');
            case 'forwarder_due_date':
                return $this->translator->trans('supplier_order.field.forwarder_due_date', [], 'EkynaCommerce');
        };

        return $name;
    }

    protected function transform(string $name, string $value): string
    {
        if ($name === 'state') {
            return SupplierOrderStates::getLabel($value)->trans($this->translator);
        }

        return $value;
    }
}
