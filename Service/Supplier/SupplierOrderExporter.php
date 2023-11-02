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
    public function __construct(
        SupplierOrderRepositoryInterface       $repository,
        protected readonly TranslatorInterface $translator
    ) {
        parent::__construct($repository);
    }

    protected function buildHeader(string $name): string
    {
        $t = fn(string $m, string $d) => $this->translator->trans($m, [], $d);

        return match ($name) {
            'number'               => $t('field.number', 'EkynaUi'),
            'state'                => $t('field.status', 'EkynaCommerce'),
            'ordered_at'           => $t('supplier_order.field.ordered_at', 'EkynaCommerce'),
            'completed_at'         => $t('field.completed_at', 'EkynaUi'),
            'supplier'             => $t('supplier.label.singular', 'EkynaCommerce'),
            'payment_total'        => $t('supplier_order.field.payment_total', 'EkynaCommerce'),
            'payment_paid_total'   => $t('supplier_order.field.payment_paid_total', 'EkynaCommerce'),
            'payment_due_date'     => $t('supplier_order.field.payment_due_date', 'EkynaCommerce'),
            'carrier'              => $t('supplier_carrier.label.singular', 'EkynaCommerce'),
            'forwarder_total'      => $t('supplier_order.field.forwarder_total', 'EkynaCommerce'),
            'forwarder_paid_total' => $t('supplier_order.field.forwarder_paid_total', 'EkynaCommerce'),
            'forwarder_due_date'   => $t('supplier_order.field.forwarder_due_date', 'EkynaCommerce'),
            default                => $name,
        };
    }

    protected function transform(string $name, string $value): string
    {
        // TODO Move to map (override getDefaultMap)
        if ($name === 'state') {
            return SupplierOrderStates::getLabel($value)->trans($this->translator);
        }

        return $value;
    }
}
