<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\CommerceBundle\Model\InvoiceTypes;
use Ekyna\Bundle\CommerceBundle\Table\Action\InvoiceDocumentActionType;
use Ekyna\Bundle\CommerceBundle\Table\Column;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;

/**
 * Class OrderInvoiceType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderInvoiceType extends AbstractOrderListType
{
    /**
     * @inheritDoc
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        parent::buildTable($builder, $options);

        $builder
            ->addDefaultSort('createdAt', ColumnSort::DESC)
            ->addColumn('number', Column\OrderInvoiceType::class, [
                'label'         => 'ekyna_core.field.number',
                'property_path' => false,
                'position'      => 10,
            ])
            ->addColumn('type', Column\InvoiceTypeType::class, [
                'label'    => 'ekyna_core.field.type',
                'position' => 20,
            ])
            ->addColumn('grandTotal', BType\Column\PriceType::class, [
                'label'         => 'ekyna_commerce.sale.field.ati_total',
                'currency_path' => 'currency',
                'position'      => 50,
            ])
            ->addColumn('saleTotal', Column\CurrencyType::class, [
                'label'         => 'ekyna_commerce.invoice.field.sale_total',
                'property_path' => 'order.grandTotal',
                'subject_path'  => 'order',
                'position'      => 60,
            ])
            ->addColumn('paidTotal', Column\InvoicePaidTotalType::class, [
                'label'         => 'ekyna_commerce.invoice.field.sale_paid',
                'property_path' => false,
                'position'      => 70,
            ])
            ->addColumn('dueDate', CType\Column\DateTimeType::class, [
                'label'       => 'ekyna_commerce.sale.field.outstanding_date',
                'time_format' => 'none',
                'position'    => 80,
            ])
            ->addColumn('createdAt', CType\Column\DateTimeType::class, [
                'label'       => 'ekyna_core.field.created_at',
                'time_format' => 'none',
                'position'    => 90,
            ]);

        if ($options['order'] || $options['customer']) {
            return;
        }

        $builder
            ->addFilter('number', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.number',
                'position' => 10,
            ])
            ->addFilter('type', CType\Filter\ChoiceType::class, [
                'label'    => 'ekyna_core.field.type',
                'choices'  => InvoiceTypes::getChoices(),
                'position' => 20,
            ])
            // TODO Customer
            ->addFilter('grandTotal', CType\Filter\NumberType::class, [
                'label'    => 'ekyna_commerce.sale.field.ati_total',
                'position' => 50,
            ])
            ->addFilter('saleTotal', CType\Filter\NumberType::class, [
                'label'         => 'ekyna_commerce.invoice.field.sale_total',
                'property_path' => 'order.grandTotal',
                'position'      => 50,
            ])
            ->addFilter('outstandingDate', CType\Filter\DateTimeType::class, [
                'label'         => 'ekyna_commerce.sale.field.outstanding_date',
                'property_path' => 'order.outstandingDate',
                'position'      => 60,
            ])
            ->addFilter('createdAt', CType\Filter\DateTimeType::class, [
                'label'    => 'ekyna_core.field.created_at',
                'position' => 70,
            ]);

        $builder->addAction('documents', InvoiceDocumentActionType::class, [
            'label' => 'Afficher les factures/avoirs', // TODO trans
        ]);
    }
}
