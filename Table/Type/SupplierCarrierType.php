<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type as DType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class SupplierCarrierType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierCarrierType extends ResourceTableType
{
    /**
     * {@inheritdoc}
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
            ->addColumn('name', BType\Column\AnchorType::class, [
                'label'                => 'ekyna_core.field.name',
                'route_name'           => 'ekyna_commerce_supplier_carrier_admin_show',
                'route_parameters_map' => [
                    'supplierCarrierId' => 'id',
                ],
                'position'             => 10,
            ])
            ->addColumn('tax', DType\Column\EntityType::class, [
                'label'                => 'ekyna_commerce.tax.label.singular',
                'entity_label'         => 'name',
                'route_name'           => 'ekyna_commerce_tax_admin_show',
                'route_parameters_map' => [
                    'taxId' => 'id',
                ],
                'position'             => 20,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_commerce_supplier_carrier_admin_edit',
                        'route_parameters_map' => [
                            'supplierCarrierId' => 'id',
                        ],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_commerce_supplier_carrier_admin_remove',
                        'route_parameters_map' => [
                            'supplierCarrierId' => 'id',
                        ],
                        'permission'           => 'delete',
                    ],
                ],
            ])
            ->addFilter('name', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.name',
                'position' => 10,
            ]);
    }
}
