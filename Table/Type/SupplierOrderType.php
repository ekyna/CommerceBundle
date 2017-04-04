<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\CommerceBundle\Model\SupplierOrderStates;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class SupplierOrderType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderType extends ResourceTableType
{
    /**
     * {@inheritdoc}
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
            ->addColumn('number', 'anchor', [
                'label'                => 'ekyna_core.field.number',
                'sortable'             => true,
                'route_name'           => 'ekyna_commerce_supplier_order_admin_show',
                'route_parameters_map' => [
                    'supplierOrderId' => 'id',
                ],
                'position'             => 10,
            ])
            ->addColumn('state', 'ekyna_commerce_supplier_order_state', [
                'label'    => 'ekyna_commerce.supplier_order.field.state',
                'position' => 20,
            ])
            ->addColumn('actions', 'admin_actions', [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_commerce_supplier_order_admin_edit',
                        'route_parameters_map' => [
                            'supplierOrderId' => 'id',
                        ],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_commerce_supplier_order_admin_remove',
                        'route_parameters_map' => [
                            'supplierOrderId' => 'id',
                        ],
                        'permission'           => 'delete',
                    ],
                ],
            ])
            ->addFilter('number', 'text', [
                'label'    => 'ekyna_core.field.number',
                'position' => 10,
            ])
            ->addFilter('state', 'choice', [
                'label'    => 'ekyna_commerce.supplier_order.field.state',
                'choices'  => SupplierOrderStates::getChoices(),
                'position' => 20,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_commerce_supplier_order';
    }
}
