<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class CurrencyType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CurrencyType extends ResourceTableType
{
    /**
     * {@inheritdoc}
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
            ->addColumn('name', BType\Column\AnchorType::class, [
                'label'                => 'ekyna_core.field.name',
                'property_path'        => null,
                'sortable'             => true,
                'route_name'           => 'ekyna_commerce_currency_admin_show',
                'route_parameters_map' => ['currencyId' => 'id'],
                'position'             => 10,
            ])
            ->addColumn('code', CType\Column\TextType::class, [
                'label'    => 'ekyna_core.field.code',
                'sortable' => true,
                'position' => 20,
            ])
            ->addColumn('enabled', CType\Column\BooleanType::class, [
                'label'                => 'ekyna_core.field.enabled',
                'sortable'             => true,
                'route_name'           => 'ekyna_commerce_currency_admin_toggle',
                'route_parameters'     => ['field' => 'enabled'],
                'route_parameters_map' => ['currencyId' => 'id'],
                'position'             => 30,
            ])
            ->addColumn('default', CType\Column\BooleanType::class, [
                'label'                 => 'ekyna_core.field.default',
                'sortable'              => true,
                'route_name'            => 'ekyna_commerce_currency_admin_toggle',
                'route_parameters'      => ['field' => 'default'],
                'route_parameters_map'  => ['currencyId' => 'id'],
                'true_class'            => 'label-primary',
                'false_class'           => 'label-default',
                'disable_property_path' => 'default',
                'position'              => 40,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_commerce_currency_admin_edit',
                        'route_parameters_map' => ['currencyId' => 'id'],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_commerce_currency_admin_remove',
                        'route_parameters_map' => ['currencyId' => 'id'],
                        'permission'           => 'delete',
                    ],
                ],
            ])
            ->addFilter('name', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.name',
                'position' => 10,
            ])
            ->addFilter('code', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.code',
                'position' => 20,
            ])
            ->addFilter('enabled', CType\Filter\BooleanType::class, [
                'label'    => 'ekyna_core.field.enabled',
                'position' => 30,
            ])
            ->addFilter('default', CType\Filter\BooleanType::class, [
                'label'    => 'ekyna_core.field.default',
                'position' => 40,
            ]);
    }
}
