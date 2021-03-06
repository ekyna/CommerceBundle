<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class ShipmentMethodType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentMethodType extends ResourceTableType
{
    /**
     * {@inheritdoc}
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
            ->addDefaultSort('position')
            ->setSortable(false)
            ->setFilterable(false)
            ->setPerPageChoices([100])
            ->addColumn('name', BType\Column\AnchorType::class, [
                'label'                => 'ekyna_core.field.name',
                'route_name'           => 'ekyna_commerce_shipment_method_admin_show',
                'route_parameters_map' => [
                    'shipmentMethodId' => 'id',
                ],
                'position'             => 10,
            ])
            ->addColumn('available', CType\Column\BooleanType::class, [
                'label'                => 'ekyna_commerce.field.front_office',
                'route_name'           => 'ekyna_commerce_shipment_method_admin_toggle',
                'route_parameters'     => ['field' => 'available'],
                'route_parameters_map' => ['shipmentMethodId' => 'id'],
                'position'             => 20,
            ])
            ->addColumn('enabled', CType\Column\BooleanType::class, [
                'label'                => 'ekyna_core.field.enabled',
                'route_name'           => 'ekyna_commerce_shipment_method_admin_toggle',
                'route_parameters'     => ['field' => 'enabled'],
                'route_parameters_map' => ['shipmentMethodId' => 'id'],
                'position'             => 30,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.move_up',
                        'icon'                 => 'arrow-up',
                        'class'                => 'primary',
                        'route_name'           => 'ekyna_commerce_shipment_method_admin_move_up',
                        'route_parameters_map' => ['shipmentMethodId' => 'id'],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.move_down',
                        'icon'                 => 'arrow-down',
                        'class'                => 'primary',
                        'route_name'           => 'ekyna_commerce_shipment_method_admin_move_down',
                        'route_parameters_map' => ['shipmentMethodId' => 'id'],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_commerce_shipment_method_admin_edit',
                        'route_parameters_map' => ['shipmentMethodId' => 'id'],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_commerce_shipment_method_admin_remove',
                        'route_parameters_map' => ['shipmentMethodId' => 'id'],
                        'permission'           => 'delete',
                    ],
                ],
            ]);
    }
}
