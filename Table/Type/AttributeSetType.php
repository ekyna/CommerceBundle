<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class AttributeSetType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeSetType extends ResourceTableType
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
            ->addColumn('name', 'anchor', array(
                'label'                => 'ekyna_core.field.name',
                'property_path'        => null,
                'sortable'             => true,
                'route_name'           => 'ekyna_commerce_attribute_set_admin_show',
                'route_parameters_map' => ['attributeSetId' => 'id'],
            ))
            ->addColumn('actions', 'admin_actions', [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_commerce_attribute_set_admin_edit',
                        'route_parameters_map' => ['attributeSetId' => 'id'],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_commerce_attribute_set_admin_remove',
                        'route_parameters_map' => ['attributeSetId' => 'id'],
                        'permission'           => 'delete',
                    ],
                ],
            ])
            ->addFilter('id', 'number')
            ->addFilter('name', 'text', [
                'label' => 'ekyna_core.field.name',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_commerce_attribute_set';
    }
}
