<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
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
            ->addColumn('id', 'number', [
                'sortable' => true,
            ])
            ->addColumn('name', 'anchor', [
                'label'                => 'ekyna_core.field.name',
                'property_path'        => null,
                'sortable'             => true,
                'route_name'           => 'ekyna_commerce_currency_admin_show',
                'route_parameters_map' => ['currencyId' => 'id'],
            ])
            ->addColumn('code', 'text', [
                'label'    => 'ekyna_core.field.code',
                'sortable' => true,
            ])
            ->addColumn('enabled', 'boolean', [
                'label'                => 'ekyna_core.field.enabled',
                'sortable'             => true,
                'route_name'           => 'ekyna_commerce_currency_admin_toggle',
                'route_parameters'     => ['field' => 'enabled'],
                'route_parameters_map' => ['currencyId' => 'id'],
            ])
            ->addColumn('default', 'boolean', [
                'label'                 => 'ekyna_core.field.default',
                'sortable'              => true,
                'route_name'            => 'ekyna_commerce_currency_admin_toggle',
                'route_parameters'      => ['field' => 'default'],
                'route_parameters_map'  => ['currencyId' => 'id'],
                'true_class'            => 'label-primary',
                'false_class'           => 'label-default',
                'disable_property_path' => 'default',
            ])
            ->addColumn('actions', 'admin_actions', [
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
            ->addFilter('id', 'number')
            ->addFilter('name', 'text', [
                'label' => 'ekyna_core.field.name',
            ])
            ->addFilter('code', 'text', [
                'label' => 'ekyna_core.field.code',
            ])
            ->addFilter('enabled', 'boolean', [
                'label' => 'ekyna_core.field.enabled',
            ])
            ->addFilter('default', 'boolean', [
                'label' => 'ekyna_core.field.default',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_commerce_currency';
    }
}
