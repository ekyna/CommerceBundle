<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\CommerceBundle\Table\Column\VatDisplayModeType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
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
                'position'              => 20,
            ])
            ->addColumn('registration', CType\Column\BooleanType::class, [
                'label'                => 'ekyna_commerce.customer_group.field.registration',
                'route_name'           => 'ekyna_commerce_customer_group_admin_toggle',
                'route_parameters'     => ['field' => 'registration'],
                'route_parameters_map' => ['customerGroupId' => 'id'],
                'true_class'           => 'label-primary',
                'false_class'          => 'label-default',
                'disable_property_path' => 'default',
                'position'             => 30,
            ])
            ->addColumn('quoteAllowed', CType\Column\BooleanType::class, [
                'label'                 => 'ekyna_commerce.customer_group.field.quote_allowed',
                'route_name'            => 'ekyna_commerce_customer_group_admin_toggle',
                'route_parameters'      => ['field' => 'quoteAllowed'],
                'route_parameters_map'  => ['customerGroupId' => 'id'],
                'true_class'            => 'label-warning',
                'false_class'           => 'label-default',
                'disable_property_path' => 'default',
                'position'              => 40,
            ])
            ->addColumn('loyalty', CType\Column\BooleanType::class, [
                'label'                 => 'ekyna_commerce.customer.field.loyalty_points',
                'route_name'            => 'ekyna_commerce_customer_group_admin_toggle',
                'route_parameters'      => ['field' => 'loyalty'],
                'route_parameters_map'  => ['customerGroupId' => 'id'],
                'true_class'            => 'label-success',
                'false_class'           => 'label-default',
                'position'              => 50,
            ])
            ->addColumn('vatDisplayMode', VatDisplayModeType::class, [
                'position' => 60,
            ])
            ->addColumn('default', CType\Column\BooleanType::class, [
                'label'                 => 'ekyna_core.field.default',
                'route_name'            => 'ekyna_commerce_customer_group_admin_toggle',
                'route_parameters'      => ['field' => 'default'],
                'route_parameters_map'  => ['customerGroupId' => 'id'],
                'true_class'            => 'label-primary',
                'false_class'           => 'label-default',
                'disable_property_path' => 'default',
                'position'              => 70,
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
                        'label'                 => 'ekyna_core.button.remove',
                        'class'                 => 'danger',
                        'route_name'            => 'ekyna_commerce_customer_group_admin_remove',
                        'route_parameters_map'  => ['customerGroupId' => 'id'],
                        'permission'            => 'delete',
                        'disable_property_path' => 'default',
                    ],
                ],
            ])
            ->addFilter('name', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.name',
                'position' => 10,
            ]);
    }
}
