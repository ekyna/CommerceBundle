<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type as DType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\View;

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
            ->addColumn('locale', CType\Column\TextType::class, [
                'label'    => 'ekyna_core.field.locale',
                'position' => 50,
            ])
            ->addColumn('tax', DType\Column\EntityType::class, [
                'label'                => 'ekyna_commerce.tax.label.singular',
                'entity_label'         => 'name',
                'route_name'           => 'ekyna_commerce_tax_admin_show',
                'route_parameters_map' => [
                    'taxId' => 'id',
                ],
                'position'             => 60,
            ])
            ->addColumn('carrier', DType\Column\EntityType::class, [
                'label'                => 'ekyna_commerce.supplier_carrier.label.singular',
                'entity_label'         => 'name',
                'route_name'           => 'ekyna_commerce_supplier_carrier_admin_show',
                'route_parameters_map' => [
                    'supplierCarrierId' => 'id',
                ],
                'position'             => 70,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'buttons' => [
                    [
                        'label'                => 'ekyna_commerce.supplier_product.button.new',
                        'icon'                 => 'plus',
                        'class'                => 'primary',
                        'route_name'           => 'ekyna_commerce_supplier_product_admin_new',
                        'route_parameters_map' => ['supplierId' => 'id'],
                        'permission'           => 'create',
                    ],
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

    /**
     * @inheritDoc
     */
    public function buildRowView(View\RowView $view, RowInterface $row, array $options)
    {
        $view->vars['attr']['data-summary'] = json_encode([
            'route'      => 'ekyna_commerce_supplier_admin_summary',
            'parameters' => ['supplierId' => $row->getData('id')],
        ]);
    }
}
