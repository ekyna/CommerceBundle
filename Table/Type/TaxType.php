<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class TaxType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxType extends ResourceTableType
{
    /**
     * {@inheritdoc}
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
            ->addColumn('name', 'anchor', [
                'label'                => 'ekyna_core.field.name',
                'sortable'             => true,
                'route_name'           => 'ekyna_commerce_tax_admin_show',
                'route_parameters_map' => [
                    'taxId' => 'id',
                ],
                'position'             => 10,
            ])
            ->addColumn('rate', 'number', [
                'label'    => 'ekyna_core.field.rate',
                'sortable' => true,
                'position' => 20,
            ])
            ->addColumn('country', 'anchor', [
                'label'                => 'ekyna_core.field.country',
                'sortable'             => true,
                'route_name'           => 'ekyna_commerce_country_admin_show',
                'route_parameters_map' => [
                    'countryId' => 'country.id',
                ],
                'position'             => 30,
            ])
            ->addColumn('actions', 'admin_actions', [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_commerce_tax_admin_edit',
                        'route_parameters_map' => [
                            'taxId' => 'id',
                        ],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_commerce_tax_admin_remove',
                        'route_parameters_map' => [
                            'taxId' => 'id',
                        ],
                        'permission'           => 'delete',
                    ],
                ],
            ])
            ->addFilter('name', 'text', [
                'label'    => 'ekyna_core.field.name',
                'position' => 10,
            ])
            ->addFilter('rate', 'number', [
                'label'    => 'ekyna_core.field.rate',
                'position' => 20,
            ])
            /* TODO ->addFilter('country', 'entity', [
                'label'    => 'ekyna_core.field.country',
                'class'    => $this->countryClass,
                'property' => 'name',
                'position' => 30,
            ])*/;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_commerce_tax';
    }
}
