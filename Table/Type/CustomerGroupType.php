<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type as DType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class CustomerGroupType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerGroupType extends ResourceTableType
{
    /**
     * {@inheritdoc}
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
            ->addColumn('name', BType\Column\AnchorType::class, [
                'label'                => 'ekyna_core.field.name',
                'route_name'           => 'ekyna_commerce_customer_group_admin_show',
                'route_parameters_map' => ['customerGroupId' => 'id'],
                'position'             => 10,
            ])
            ->addColumn('business', CType\Column\BooleanType::class, [
                'label'                 => 'ekyna_commerce.customer_group.field.business',
                'route_name'            => 'ekyna_commerce_customer_group_admin_toggle',
                'route_parameters'      => ['field' => 'business'],
                'route_parameters_map'  => ['customerGroupId' => 'id'],
                'true_class'            => 'label-primary',
                'false_class'           => 'label-default',
                'disable_property_path' => 'business',
                'position'              => 30,
            ])
            ->addColumn('registration', CType\Column\BooleanType::class, [
                'label'                 => 'ekyna_commerce.customer_group.field.registration',
                'route_name'            => 'ekyna_commerce_customer_group_admin_toggle',
                'route_parameters'      => ['field' => 'registration'],
                'route_parameters_map'  => ['customerGroupId' => 'id'],
                'true_class'            => 'label-primary',
                'false_class'           => 'label-default',
                //'disable_property_path' => 'default',
                'position'              => 30,
            ])
            ->addColumn('default', CType\Column\BooleanType::class, [
                'label'                 => 'ekyna_core.field.default',
                'route_name'            => 'ekyna_commerce_customer_group_admin_toggle',
                'route_parameters'      => ['field' => 'default'],
                'route_parameters_map'  => ['customerGroupId' => 'id'],
                'true_class'            => 'label-primary',
                'false_class'           => 'label-default',
                'disable_property_path' => 'default',
                'position'              => 20,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_commerce_customer_group_admin_edit',
                        'route_parameters_map' => ['customerGroupId' => 'id'],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_commerce_customer_group_admin_remove',
                        'route_parameters_map' => ['customerGroupId' => 'id'],
                        'permission'           => 'delete',
                    ],
                ],
            ])
            ->addFilter('name', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.name',
                'position' => 10,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_commerce_customer_group';
    }
}
