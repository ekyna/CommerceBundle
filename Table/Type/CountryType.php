<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class CountryType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CountryType extends ResourceTableType
{
    /**
     * {@inheritdoc}
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
            ->addColumn('name', 'anchor', [
                'label'                => 'ekyna_core.field.name',
                'property_path'        => null,
                'sortable'             => true,
                'route_name'           => 'ekyna_commerce_country_admin_show',
                'route_parameters_map' => ['countryId' => 'id'],
                'position'             => 10,
            ])
            ->addColumn('code', 'text', [
                'label'    => 'ekyna_core.field.code',
                'sortable' => true,
                'position' => 20,
            ])
            ->addColumn('enabled', 'boolean', [
                'label'                => 'ekyna_core.field.enabled',
                'sortable'             => true,
                'route_name'           => 'ekyna_commerce_country_admin_toggle',
                'route_parameters'     => ['field' => 'enabled'],
                'route_parameters_map' => ['countryId' => 'id'],
                'position'             => 30,
            ])
            ->addColumn('default', 'boolean', [
                'label'                 => 'ekyna_core.field.default',
                'sortable'              => true,
                'route_name'            => 'ekyna_commerce_country_admin_toggle',
                'route_parameters'      => ['field' => 'default'],
                'route_parameters_map'  => ['countryId' => 'id'],
                'true_class'            => 'label-primary',
                'false_class'           => 'label-default',
                'disable_property_path' => 'default',
                'position'              => 40,
            ])
            ->addColumn('actions', 'admin_actions', [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_commerce_country_admin_edit',
                        'route_parameters_map' => ['countryId' => 'id'],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_commerce_country_admin_remove',
                        'route_parameters_map' => ['countryId' => 'id'],
                        'permission'           => 'delete',
                    ],
                ],
            ])
            ->addFilter('id', 'number')
            ->addFilter('name', 'text', [
                'label'    => 'ekyna_core.field.name',
                'position' => 10,
            ])
            ->addFilter('code', 'text', [
                'label'    => 'ekyna_core.field.code',
                'position' => 20,
            ])
            ->addFilter('enabled', 'boolean', [
                'label'    => 'ekyna_core.field.enabled',
                'position' => 30,
            ])
            ->addFilter('default', 'boolean', [
                'label'    => 'ekyna_core.field.default',
                'position' => 40,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_commerce_country';
    }
}
