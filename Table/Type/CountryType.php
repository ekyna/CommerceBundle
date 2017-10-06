<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;

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
            ->addDefaultSort('enabled', ColumnSort::DESC)
            ->addDefaultSort('name', ColumnSort::ASC)
            ->addColumn('name', BType\Column\AnchorType::class, [
                'label'                => 'ekyna_core.field.name',
                'property_path'        => null,
                'sortable'             => true,
                'route_name'           => 'ekyna_commerce_country_admin_show',
                'route_parameters_map' => ['countryId' => 'id'],
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
                'route_name'           => 'ekyna_commerce_country_admin_toggle',
                'route_parameters'     => ['field' => 'enabled'],
                'route_parameters_map' => ['countryId' => 'id'],
                'position'             => 30,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
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
            ]);
    }
}
