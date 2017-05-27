<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class SupplierProductType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierProductType extends ResourceTableType
{
    /**
     * {@inheritdoc}
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
            ->addColumn('designation', BType\Column\AnchorType::class, [
                'label'                => 'ekyna_core.field.designation',
                'sortable'             => true,
                'route_name'           => 'ekyna_commerce_supplier_product_admin_show',
                'route_parameters_map' => [
                    'supplierId'        => 'supplier.id',
                    'supplierProductId' => 'id',
                ],
                'position'             => 10,
            ])
            ->addColumn('reference', CType\Column\TextType::class, [
                'label'    => 'ekyna_core.field.reference',
                'sortable' => true,
                'position' => 20,
            ])
            ->addColumn('netPrice', BType\Column\PriceType::class, [
                'label'         => 'ekyna_commerce.supplier_product.field.net_price',
                'currency_path' => 'supplier.currency.code',
                'sortable'      => true,
                'position'      => 30,
            ])
            ->addColumn('weight', CType\Column\NumberType::class, [
                'label'     => 'ekyna_core.field.weight',
                'precision' => 0,
                'append'    => 'g',
                'sortable'  => true,
                'position'  => 40,
            ])
            ->addColumn('availableStock', CType\Column\NumberType::class, [
                'label'     => 'ekyna_commerce.supplier_product.field.available',
                'precision' => 0,
                'sortable'  => true,
                'position'  => 50,
            ])
            ->addColumn('orderedStock', CType\Column\NumberType::class, [
                'label'     => 'ekyna_commerce.supplier_product.field.ordered',
                'precision' => 0,
                'sortable'  => true,
                'position'  => 60,
            ])
            ->addColumn('estimatedDateOfArrival', CType\Column\DateTimeType::class, [
                'label'       => 'ekyna_commerce.supplier_product.field.eda',
                'time_format' => 'none',
                'sortable'    => true,
                'position'    => 70,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_commerce_supplier_product_admin_edit',
                        'route_parameters_map' => [
                            'supplierId'        => 'supplier.id',
                            'supplierProductId' => 'id',
                        ],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_commerce_supplier_product_admin_remove',
                        'route_parameters_map' => [
                            'supplierId'        => 'supplier.id',
                            'supplierProductId' => 'id',
                        ],
                        'permission'           => 'delete',
                    ],
                ],
            ])
            ->addFilter('designation', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.designation',
                'position' => 10,
            ])
            ->addFilter('reference', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.reference',
                'position' => 20,
            ])
            ->addFilter('netPrice', CType\Filter\NumberType::class, [
                'label'    => 'ekyna_commerce.supplier_product.field.net_price',
                'position' => 30,
            ])
            ->addFilter('weight', CType\Filter\NumberType::class, [
                'label'    => 'ekyna_core.field.weight',
                'position' => 40,
            ])
            ->addFilter('availableStock', CType\Filter\NumberType::class, [
                'label'    => 'ekyna_commerce.supplier_product.field.available_stock',
                'position' => 50,
            ])
            ->addFilter('orderedStock', CType\Filter\NumberType::class, [
                'label'    => 'ekyna_commerce.supplier_product.field.ordered_stock',
                'position' => 60,
            ])
            ->addFilter('estimatedDateOfArrival', CType\Filter\DateTimeType::class, [
                'label'    => 'ekyna_commerce.supplier_product.field.estimated_date_of_arrival',
                'position' => 70,
            ]);
    }
}
