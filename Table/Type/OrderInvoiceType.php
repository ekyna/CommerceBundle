<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\CommerceBundle\Table\Action\InvoiceDocumentActionType;
use Ekyna\Bundle\CommerceBundle\Table\Column\InvoiceTypeType;
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

        $filters = null === $options['order'];

        $builder
            ->addDefaultSort('createdAt', ColumnSort::DESC)
            ->addColumn('number', CType\Column\TextType::class, [
                'label'    => 'ekyna_core.field.number',
                'position' => 10,
            ])
            ->addColumn('type', InvoiceTypeType::class, [
                'label'    => 'ekyna_core.field.type',
                'position' => 20,
            ])
            ->addColumn('goodsBase', BType\Column\PriceType::class, [
                'label'         => 'ekyna_commerce.invoice.field.goods_base',
                'currency_path' => 'currency',
                'position'      => 30,
            ])
            ->addColumn('shipmentBase', BType\Column\PriceType::class, [
                'label'         => 'ekyna_commerce.invoice.field.shipment_base',
                'currency_path' => 'currency',
                'position'      => 40,
            ])
            ->addColumn('taxesTotal', BType\Column\PriceType::class, [
                'label'         => 'ekyna_commerce.invoice.field.taxes_total',
                'currency_path' => 'currency',
                'position'      => 50,
            ])
            ->addColumn('grandTotal', BType\Column\PriceType::class, [
                'label'         => 'ekyna_commerce.invoice.field.grand_total',
                'currency_path' => 'currency',
                'position'      => 60,
            ])
            ->addColumn('createdAt', CType\Column\DateTimeType::class, [
                'label'       => 'ekyna_core.field.created_at',
                'time_format' => 'none',
                'position'    => 70,
            ]);

        if ($filters) {
            $builder->addFilter('number', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.number',
                'position' => 10,
            ]);
        }

        $builder->addAction('documents', InvoiceDocumentActionType::class, [
            'label' => 'Afficher les factures/avoirs', // TODO trans
        ]);
    }
}
