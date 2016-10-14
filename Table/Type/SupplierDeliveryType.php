<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class SupplierProductType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierDeliveryType extends ResourceTableType
{
    /**
     * {@inheritdoc}
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
            ->addColumn('id', 'number', [
                'sortable' => true,
            ])
            /*->addColumn('designation', 'anchor', [
                'label'                => 'ekyna_core.field.designation',
                'sortable'             => true,
                'route_name'           => 'ekyna_commerce_supplier_product_admin_show',
                'route_parameters_map' => [
                    'supplierId'        => 'supplier.id',
                    'supplierProductId' => 'id',
                ],
            ])
            ->addColumn('reference', 'text', [
                'label'    => 'ekyna_core.field.reference',
                'sortable' => true,
            ])
            ->addColumn('netPrice', 'price', [
                'label'         => 'ekyna_commerce.supplier_product.field.net_price',
                'currency_path' => 'supplier.currency.code',
                'sortable'      => true,
            ])
            ->addColumn('weight', 'number', [
                'label'     => 'ekyna_core.field.weight',
                'precision' => 0,
                'append'    => 'g',
                'sortable'  => true,
            ])
            ->addColumn('availableStock', 'number', [
                'label'     => 'ekyna_commerce.supplier_product.field.available',
                'precision' => 0,
                'sortable'  => true,
            ])
            ->addColumn('orderedStock', 'number', [
                'label'     => 'ekyna_commerce.supplier_product.field.ordered',
                'precision' => 0,
                'sortable'  => true,
            ])
            ->addColumn('estimatedDateOfArrival', 'datetime', [
                'label'       => 'ekyna_commerce.supplier_product.field.eda',
                'time_format' => 'none',
                'sortable'    => true,
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
                'label' => 'ekyna_core.field.designation',
            ])
            ->addFilter('reference', 'text', [
                'label' => 'ekyna_core.field.reference',
            ])
            ->addFilter('netPrice', 'number', [
                'label' => 'ekyna_commerce.supplier_product.field.net_price',
            ])
            ->addFilter('weight', 'number', [
                'label' => 'ekyna_core.field.weight',
            ])
            ->addFilter('availableStock', 'number', [
                'label' => 'ekyna_commerce.supplier_product.field.available_stock',
            ])
            ->addFilter('orderedStock', 'number', [
                'label' => 'ekyna_commerce.supplier_product.field.ordered_stock',
            ])
            ->addFilter('estimatedDateOfArrival', 'datetime', [
                'label' => 'ekyna_commerce.supplier_product.field.estimated_date_of_arrival',
            ])*/;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_commerce_supplier_delivery';
    }
}
