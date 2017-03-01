<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Component\Table\TableBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->addColumn('id', 'id', [
                'sortable' => true,
            ])
            ->addColumn('name', 'anchor', [
                'label'                => 'ekyna_core.field.name',
                'sortable'             => true,
                'route_name'           => 'ekyna_commerce_shipment_method_admin_show',
                'route_parameters_map' => [
                    'shipmentMethodId' => 'id',
                ],
                'position'             => 10,
            ])
            ->addColumn('available', 'boolean', [
                'label'                => 'ekyna_commerce.shipment_method.field.available',
                'route_name'           => 'ekyna_commerce_shipment_method_admin_toggle',
                'route_parameters'     => ['field' => 'available'],
                'route_parameters_map' => ['shipmentMethodId' => 'id'],
                'position'             => 20,
            ])
            ->addColumn('enabled', 'boolean', [
                'label'                => 'ekyna_core.field.enabled',
                'route_name'           => 'ekyna_commerce_shipment_method_admin_toggle',
                'route_parameters'     => ['field' => 'enabled'],
                'route_parameters_map' => ['shipmentMethodId' => 'id'],
                'position'             => 30,
            ])
            ->addColumn('actions', 'admin_actions', [
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

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'default_sorts' => ['position asc'],
            'max_per_page'  => 100,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_commerce_shipment_method';
    }
}
