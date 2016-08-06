<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Component\Commerce\Product\Model\ProductTypes;
use Ekyna\Component\Table\TableBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProductType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductType extends ResourceTableType
{
    /**
     * {@inheritdoc}
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $variantMode = null !== $options['variable'];

        $builder
            ->addColumn('id', 'number', [
                'sortable' => !$variantMode,
            ]);

        if (!$variantMode) {
            $builder
                ->addColumn('type', 'ekyna_commerce_product_type', [
                    'label'    => 'ekyna_core.field.type',
                    'sortable' => true,
                ]);
        }

        $builder
            ->addColumn('designation', 'anchor', [
                'label'                => 'ekyna_core.field.designation',
                'sortable'             => !$variantMode,
                'route_name'           => 'ekyna_commerce_product_admin_show',
                'route_parameters_map' => [
                    'productId' => 'id',
                ],
            ])
            ->addColumn('reference', 'text', [
                'label'    => 'ekyna_core.field.reference',
                'sortable' => !$variantMode,
            ])
            ->addColumn('netPrice', 'price', [
                'label'    => 'ekyna_commerce.product.field.net_price',
                'currency' => 'EUR', // TODO
                'sortable' => !$variantMode,
            ])
            ->addColumn('taxGroup', 'anchor', [
                'label'                => 'ekyna_commerce.tax_group.label.singular',
                'sortable'             => !$variantMode,
                'route_name'           => 'ekyna_commerce_tax_group_admin_show',
                'route_parameters_map' => [
                    'taxGroupId' => 'taxGroup.id',
                ],
            ])
            ->addColumn('actions', 'admin_actions', [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_commerce_product_admin_edit',
                        'route_parameters_map' => [
                            'productId' => 'id',
                        ],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_commerce_product_admin_remove',
                        'route_parameters_map' => [
                            'productId' => 'id',
                        ],
                        'permission'           => 'delete',
                    ],
                ],
            ]);

        if (null === $options['variable']) {
            $builder
                ->addFilter('designation', 'text', [
                    'label' => 'ekyna_core.field.designation',
                ])
                ->addFilter('netPrice', 'number', [
                    'label' => 'ekyna_core.field.net_price',
                ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'variable'     => null,
            'customize_qb' => function (Options $options) {
                /** @var \Ekyna\Component\Commerce\Product\Model\ProductInterface $variable */
                if (null !== $variable = $options['variable']) {
                    ProductTypes::assertVariable($variable);

                    return function (QueryBuilder $qb, $alias) use ($variable) {
                        $qb
                            ->andWhere($alias . '.parent = :parent')
                            ->andWhere($alias . '.type = :type')
                            ->setParameter('parent', $variable)
                            ->setParameter('type', ProductTypes::TYPE_VARIANT);
                    };
                }

                return function (QueryBuilder $qb, $alias) {
                    $qb
                        ->andWhere($alias . '.type != :type')
                        ->setParameter('type', ProductTypes::TYPE_VARIANT);
                };
            },
        ]);

        $resolver
            ->setAllowedTypes('variable', ['null', 'Ekyna\Component\Commerce\Product\Model\ProductInterface']);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_commerce_product';
    }
}
