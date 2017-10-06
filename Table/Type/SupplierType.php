<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type\Column\EntityType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class SupplierType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierType extends ResourceTableType
{
    /**
     * {@inheritdoc}
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
            ->addColumn('name', BType\Column\AnchorType::class, [
                'label'                => 'ekyna_core.field.name',
                'route_name'           => 'ekyna_commerce_supplier_admin_show',
                'route_parameters_map' => [
                    'supplierId' => 'id',
                ],
                'position'             => 10,
            ])
            ->addColumn('email', CType\Column\TextType::class, [
                'label'    => 'ekyna_core.field.email',
                'position' => 20,
            ])
            ->addColumn('customerCode', CType\Column\TextType::class, [
                'label'    => 'ekyna_commerce.supplier.field.customer_code',
                'position' => 30,
            ])
            ->addColumn('currency', CType\Column\TextType::class, [
                'label'         => 'ekyna_core.field.currency',
                'property_path' => 'currency.code',
                'position'      => 40,
            ])
            ->addColumn('carrier', EntityType::class, [
                'label'                => 'ekyna_commerce.supplier_carrier.label.singular',
                'entity_label'         => 'name',
                'route_name'           => 'ekyna_commerce_supplier_carrier_admin_show',
                'route_parameters_map' => [
                    'supplierCarrierId' => 'id',
                ],
                'position'             => 50,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_commerce_supplier_admin_edit',
                        'route_parameters_map' => ['supplierId' => 'id'],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_commerce_supplier_admin_remove',
                        'route_parameters_map' => ['supplierId' => 'id'],
                        'permission'           => 'delete',
                    ],
                ],
            ])
            ->addFilter('name', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.name',
                'position' => 10,
            ])
            ->addFilter('email', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.email',
                'position' => 20,
            ])
            ->addFilter('customerCode', CType\Filter\TextType::class, [
                'label'    => 'ekyna_commerce.supplier.field.customer_code',
                'position' => 30,
            ]);
    }
}
