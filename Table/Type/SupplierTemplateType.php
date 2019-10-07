<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class SupplierTemplateType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierTemplateType extends ResourceTableType
{
    /**
     * {@inheritdoc}
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
            ->addColumn('title', BType\Column\AnchorType::class, [
                'label'                => 'ekyna_core.field.title',
                'route_name'           => 'ekyna_commerce_supplier_template_admin_show',
                'route_parameters_map' => [
                    'supplierTemplateId' => 'id',
                ],
                'position'             => 10,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_commerce_supplier_template_admin_edit',
                        'route_parameters_map' => ['supplierTemplateId' => 'id'],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_commerce_supplier_template_admin_remove',
                        'route_parameters_map' => ['supplierTemplateId' => 'id'],
                        'permission'           => 'delete',
                    ],
                ],
            ])
            ->addFilter('title', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.title',
                'position' => 10,
            ]);
    }
}
