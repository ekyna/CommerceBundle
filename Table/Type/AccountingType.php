<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class AccountingType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AccountingType extends ResourceTableType
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
            ->addColumn('number', BType\Column\AnchorType::class, [
                'label'                => 'ekyna_core.field.number',
                'property_path'        => null,
                'sortable'             => true,
                'route_name'           => 'ekyna_commerce_accounting_admin_show',
                'route_parameters_map' => ['accountingId' => 'id'],
                'position'             => 10,
            ])
            ->addColumn('name', CType\Column\TextType::class, [
                'label'    => 'ekyna_core.field.name',
                'sortable' => true,
                'position' => 20,
            ])
            ->addColumn('enabled', CType\Column\BooleanType::class, [
                'label'                => 'ekyna_core.field.enabled',
                'sortable'             => true,
                'route_name'           => 'ekyna_commerce_accounting_admin_toggle',
                'route_parameters'     => ['field' => 'enabled'],
                'route_parameters_map' => ['accountingId' => 'id'],
                'position'             => 30,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_commerce_accounting_admin_edit',
                        'route_parameters_map' => ['accountingId' => 'id'],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_commerce_accounting_admin_remove',
                        'route_parameters_map' => ['accountingId' => 'id'],
                        'permission'           => 'delete',
                    ],
                ],
            ])
            ->addFilter('number', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.number',
                'position' => 10,
            ])
            ->addFilter('name', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.name',
                'position' => 20,
            ])
            ->addFilter('enabled', CType\Filter\BooleanType::class, [
                'label'    => 'ekyna_core.field.enabled',
                'position' => 30,
            ]);
    }
}
