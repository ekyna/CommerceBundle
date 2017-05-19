<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class AbstractStockUnitType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 * @deprecated Use the StockRenderer
 */
abstract class AbstractStockUnitType extends ResourceTableType
{
    /**
     * @inheritdoc
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
            ->addColumn('state', 'text', [
                'label'    => 'ekyna_core.field.status',
                'position' => 10,
            ])
            ->addColumn('geocode', 'text', [
                'label'    => 'ekyna_commerce.stock_unit.field.geocode',
                'position' => 20,
            ])
            ->addColumn('orderedQuantity', 'number', [
                'label'    => 'ekyna_commerce.stock_unit.field.ordered_quantity',
                'position' => 30,
            ])
            ->addColumn('receivedQuantity', 'number', [
                'label'    => 'ekyna_commerce.stock_unit.field.received_quantity',
                'position' => 40,
            ])
            ->addColumn('shippedQuantity', 'number', [
                'label'    => 'ekyna_commerce.stock_unit.field.shipped_quantity',
                'position' => 50,
            ])
            ->addColumn('estimatedDateOfArrival', 'datetime', [
                'label'    => 'ekyna_commerce.stock_unit.field.estimated_date_of_arrival',
                'position' => 60,
            ])
            ->addColumn('netPrice', 'price', [
                'label'    => 'ekyna_commerce.stock_unit.field.net_price',
                'currency' => 'EUR',
                // TODO 'currency_path' => 'supplier.currency.code',
                'position' => 70,
            ]);
    }
}
