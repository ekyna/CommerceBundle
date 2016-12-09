<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
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
            /*->addColumn('id', 'id', [
                'sortable' => true,
            ])*/
            ->addColumn('designation', 'anchor', [
                'label'                => 'ekyna_core.field.designation',
                'sortable'             => true,
                'route_name'           => 'ekyna_commerce_supplier_product_admin_show',
                'route_parameters_map' => [
                    'supplierId'        => 'supplier.id',
                    'supplierProductId' => 'id',
                ],
                'position'             => 10,
            ])
            ->addColumn('reference', 'text', [
                'label'    => 'ekyna_core.field.reference',
                'sortable' => true,
                'position' => 20,
            ])
            ->addColumn('netPrice', 'price', [
                'label'         => 'ekyna_commerce.supplier_product.field.net_price',
                'currency_path' => 'supplier.currency.code',
                'sortable'      => true,
                'position'      => 30,
            ])
            ->addColumn('weight', 'number', [
                'label'     => 'ekyna_core.field.weight',
                'precision' => 0,
                'append'    => 'g',
                'sortable'  => true,
                'position'  => 40,
            ])
            ->addColumn('availableStock', 'number', [
                'label'     => 'ekyna_commerce.supplier_product.field.available',
                'precision' => 0,
                'sortable'  => true,
                'position'  => 50,
            ])
            ->addColumn('orderedStock', 'number', [
                'label'     => 'ekyna_commerce.supplier_product.field.ordered',
                'precision' => 0,
                'sortable'  => true,
                'position'  => 60,
            ])
            ->addColumn('estimatedDateOfArrival', 'datetime', [
                'label'       => 'ekyna_commerce.supplier_product.field.eda',
                'time_format' => 'none',
                'sortable'    => true,
                'position'    => 70,
            ])
            ->addColumn('actions', 'admin_actions', [
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
            ->addFilter('designation', 'text', [
                'label'    => 'ekyna_core.field.designation',
                'position' => 10,
            ])
            ->addFilter('reference', 'text', [
                'label'    => 'ekyna_core.field.reference',
                'position' => 20,
            ])
            ->addFilter('netPrice', 'number', [
                'label'    => 'ekyna_commerce.supplier_product.field.net_price',
                'position' => 30,
            ])
            ->addFilter('weight', 'number', [
                'label'    => 'ekyna_core.field.weight',
                'position' => 40,
            ])
            ->addFilter('availableStock', 'number', [
                'label'    => 'ekyna_commerce.supplier_product.field.available_stock',
                'position' => 50,
            ])
            ->addFilter('orderedStock', 'number', [
                'label'    => 'ekyna_commerce.supplier_product.field.ordered_stock',
                'position' => 60,
            ])
            ->addFilter('estimatedDateOfArrival', 'datetime', [
                'label'    => 'ekyna_commerce.supplier_product.field.estimated_date_of_arrival',
                'position' => 70,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_commerce_supplier_product';
    }
}
