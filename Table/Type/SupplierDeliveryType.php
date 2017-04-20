<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class SupplierProductType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierDeliveryType extends AbstractResourceType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('id', 'id', [
                'sortable' => true,
            ])
            /*->addColumn('reference', 'anchor', [
                'label'                => t('field.reference', [], 'EkynaUi'),
                'sortable'             => true,
                'route_name'           => 'ekyna_commerce_supplier_product_admin_show',
                'route_parameters_map' => [
                    'supplierId'        => 'supplier.id',
                    'supplierProductId' => 'id',
                ],
                'position' => 10,
            ])
            ->addColumn('netPrice', 'price', [
                'label'         => t('supplier_product.field.net_price', [], 'EkynaCommerce'),
                'currency_path' => 'supplier.currency.code',
                'sortable'      => true,
                'position' => 20,
            ])
            ->addColumn('weight', 'number', [
                'label'     => t('field.weight', [], 'EkynaUi'),
                'precision' => 0,
                'append'    => 'g',
                'sortable'  => true,
                'position' => 30,
            ])
            ->addColumn('actions', 'admin_actions', [
                'buttons' => [
                    [
                        'label'                => t('button.edit', [], 'EkynaUi'),
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_commerce_supplier_product_admin_edit',
                        'route_parameters_map' => [
                            'supplierId'        => 'supplier.id',
                            'supplierProductId' => 'id',
                        ],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => t('button.remove', [], 'EkynaUi'),
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_commerce_supplier_product_admin_remove',
                        'route_parameters_map' => [
                            'supplierId'        => 'supplier.id',
                            'supplierProductId' => 'id',
                        ],
                        'permission'           => 'delete',
                    ],
                ],
            ])
            ->addFilter('reference', 'text', [
                'label' => t('field.reference', [], 'EkynaUi'),
                'position' => 10,
            ])
            ->addFilter('netPrice', 'number', [
                'label' => t('supplier_product.field.net_price', [], 'EkynaCommerce'),
                'position' => 20,
            ])
            ->addFilter('weight', 'number', [
                'label' => t('field.weight', [], 'EkynaUi'),
                'position' => 30,
            ])*/;
    }
}
