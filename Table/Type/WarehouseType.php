<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Commerce\Common\Entity\Country;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type as DType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class WarehouseType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class WarehouseType extends ResourceTableType
{
    /**
     * @inheritDoc
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
            ->addColumn('name', BType\Column\AnchorType::class, [
                'label'                => 'ekyna_core.field.name',
                'route_name'           => 'ekyna_commerce_warehouse_admin_show',
                'route_parameters_map' => [
                    'warehouseId' => 'id',
                ],
                'position'             => 10,
            ])
            ->addColumn('countries', DType\Column\EntityType::class, [
                'label'                => 'ekyna_core.field.country',
                'entity_label'         => 'name',
                'route_name'           => 'ekyna_commerce_country_admin_show',
                'route_parameters_map' => [
                    'countryId' => 'id',
                ],
                'position'             => 10,
            ])
            ->addColumn('office', CType\Column\BooleanType::class, [
                'label'    => 'ekyna_commerce.warehouse.field.office',
                'position' => 30,
            ])
            ->addColumn('default', CType\Column\BooleanType::class, [
                'label'    => 'ekyna_core.field.default',
                'position' => 40,
            ])
            ->addColumn('priority', CType\Column\NumberType::class, [
                'label'     => 'ekyna_core.field.priority',
                'position'  => 50,
                'precision' => 0,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_commerce_warehouse_admin_edit',
                        'route_parameters_map' => [
                            'warehouseId' => 'id',
                        ],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_commerce_warehouse_admin_remove',
                        'route_parameters_map' => [
                            'warehouseId' => 'id',
                        ],
                        'permission'           => 'delete',
                    ],
                ],
            ])
            ->addFilter('name', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.name',
                'position' => 10,
            ])
            ->addFilter('countries', DType\Filter\EntityType::class, [
                'label'        => 'ekyna_core.field.country',
                'class'        => Country::class,
                'entity_label' => 'name',
                'position'     => 20,
            ])
            ->addFilter('office', CType\Filter\BooleanType::class, [
                'label'    => 'ekyna_commerce.warehouse.field.office',
                'position' => 30,
            ])
            ->addFilter('default', CType\Filter\BooleanType::class, [
                'label'    => 'ekyna_core.field.default',
                'position' => 40,
            ])
            ->addFilter('priority', CType\Filter\NumberType::class, [
                'label'    => 'ekyna_core.field.priority',
                'position' => 50,
            ]);
    }
}
