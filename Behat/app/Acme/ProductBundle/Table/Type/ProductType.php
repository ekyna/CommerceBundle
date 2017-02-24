<?php

namespace Acme\ProductBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class ProductType
 * @package Acme\ProductBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductType extends ResourceTableType
{
    /**
     * @inheritdoc
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
            ->addColumn('id', 'id')
            ->addColumn('designation', 'anchor', [
                'label'                => 'acme_core.field.designation',
                'route_name'           => 'acme_product_product_admin_show',
                'route_parameters_map' => [
                    'productId' => 'id',
                ],
                'position'             => 10,
            ])
            ->addColumn('reference', 'text', [
                'label'    => 'acme_core.field.reference',
                'position' => 30,
            ])
            ->addColumn('netPrice', 'price', [
                'label'    => 'acme_product.product.field.net_price',
                'currency' => 'EUR',
                'position' => 40,
            ])
            ->addColumn('actions', 'admin_actions', [
                'buttons' => [
                    [
                        'label'                => 'acme_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'acme_product_product_admin_edit',
                        'route_parameters_map' => ['productId' => 'id'],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'acme_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'acme_product_product_admin_remove',
                        'route_parameters_map' => ['productId' => 'id'],
                        'permission'           => 'delete',
                    ],
                ],
            ]);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'acme_product_product';
    }
}
