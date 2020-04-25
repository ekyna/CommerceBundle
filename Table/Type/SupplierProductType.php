<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\ResourceBundle\Table\Filter\ResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntitySource;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type as DType;
use Ekyna\Component\Table\Exception\InvalidArgumentException;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SupplierProductType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierProductType extends ResourceTableType
{
    /**
     * Builds the table for the given subject.
     *
     * @param TableBuilderInterface $builder
     * @param SubjectInterface      $subject
     */
    private function buildForSubject(TableBuilderInterface $builder, SubjectInterface $subject)
    {
        $source = $builder->getSource();
        if ($source instanceof EntitySource) {
            $source->setQueryBuilderInitializer(function (QueryBuilder $qb, $alias) use ($subject) {
                $qb
                    ->andWhere($qb->expr()->eq($alias . '.subjectIdentity.provider', ':provider'))
                    ->andWhere($qb->expr()->eq($alias . '.subjectIdentity.identifier', ':identifier'))
                    ->setParameter('provider', $subject::getProviderName())
                    ->setParameter('identifier', $subject->getId());
            });

            $builder->setPerPageChoices(['100']);
        }
    }

    /**
     * Builds the table for the given supplier.
     *
     * @param TableBuilderInterface $builder
     * @param SupplierInterface     $supplier
     */
    private function buildForSupplier(TableBuilderInterface $builder, SupplierInterface $supplier)
    {
        $builder->setExportable(true);

        $source = $builder->getSource();
        if ($source instanceof EntitySource) {
            $source->setQueryBuilderInitializer(function (QueryBuilder $qb, $alias) use ($supplier) {
                $qb
                    ->andWhere($qb->expr()->eq($alias . '.supplier', ':supplier'))
                    ->setParameter('supplier', $supplier);
            });
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $subject = $options['subject'];
        $supplier = $options['supplier'];

        if ($subject && $supplier) {
            throw new InvalidArgumentException("Please provider 'subject' or 'supplier' option, but not both.");
        }

        if (null !== $subject) {
            $this->buildForSubject($builder, $subject);
        } else {
            $builder
                ->addColumn('designation', BType\Column\AnchorType::class, [
                    'label'                => 'ekyna_core.field.designation',
                    'sortable'             => true,
                    'route_name'           => 'ekyna_commerce_supplier_product_admin_show',
                    'route_parameters_map' => [
                        'supplierId'        => 'supplier.id',
                        'supplierProductId' => 'id',
                    ],
                    'position'             => 0,
                ])
                ->addFilter('designation', CType\Filter\TextType::class, [
                    'label'    => 'ekyna_core.field.designation',
                    'position' => 10,
                ]);
        }

        if (null !== $supplier) {
            $this->buildForSupplier($builder, $supplier);
        } else {
            $builder
                ->addColumn('supplier', BType\Column\AnchorType::class, [
                    'label'                => 'ekyna_commerce.supplier.label.singular',
                    'sortable'             => true,
                    'route_name'           => 'ekyna_commerce_supplier_product_admin_show',
                    'route_parameters_map' => [
                        'supplierId'        => 'supplier.id',
                        'supplierProductId' => 'id',
                    ],
                    'position'             => 0,
                ])
                ->addFilter('supplier', ResourceType::class, [
                    'resource' => 'ekyna_commerce.supplier',
                    'position' => 10,
                ]);
        }

        $builder
            ->addColumn('reference', CType\Column\TextType::class, [
                'label'    => 'ekyna_core.field.reference',
                'sortable' => true,
                'position' => 20,
            ])
            ->addColumn('netPrice', BType\Column\PriceType::class, [
                'label'         => 'ekyna_commerce.field.buy_net_price',
                'currency_path' => 'supplier.currency.code',
                'sortable'      => true,
                'position'      => 30,
            ])
            ->addColumn('taxGroup', DType\Column\EntityType::class, [
                'label'                => 'ekyna_commerce.tax_group.label.singular',
                'entity_label'         => 'name',
                'route_name'           => 'ekyna_commerce_tax_group_admin_show',
                'route_parameters_map' => ['taxGroupId' => 'id'],
                'position'             => 40,
            ])
            ->addColumn('weight', CType\Column\NumberType::class, [
                'label'     => 'ekyna_core.field.weight',
                'precision' => 3,
                'append'    => 'kg',
                'sortable'  => true,
                'position'  => 50,
            ])
            ->addColumn('availableStock', CType\Column\NumberType::class, [
                'label'     => 'ekyna_commerce.supplier_product.field.available',
                'precision' => 0,
                'sortable'  => true,
                'position'  => 60,
            ])
            ->addColumn('orderedStock', CType\Column\NumberType::class, [
                'label'     => 'ekyna_commerce.supplier_product.field.ordered',
                'precision' => 0,
                'sortable'  => true,
                'position'  => 70,
            ])
            ->addColumn('estimatedDateOfArrival', CType\Column\DateTimeType::class, [
                'label'       => 'ekyna_commerce.supplier_product.field.eda',
                'time_format' => 'none',
                'sortable'    => true,
                'position'    => 80,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'icon'                 => 'pencil',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_commerce_supplier_product_admin_edit',
                        'route_parameters_map' => [
                            'supplierId'        => 'supplier.id',
                            'supplierProductId' => 'id',
                        ],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'icon'                 => 'trash',
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
            ->addFilter('reference', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.reference',
                'position' => 20,
            ])
            ->addFilter('netPrice', CType\Filter\NumberType::class, [
                'label'    => 'ekyna_commerce.field.buy_net_price',
                'position' => 30,
            ])
            ->addFilter('taxGroup', ResourceType::class, [
                'resource' => 'ekyna_commerce.tax_group',
                'position' => 40,
            ])
            ->addFilter('weight', CType\Filter\NumberType::class, [
                'label'    => 'ekyna_core.field.weight',
                'position' => 50,
            ])
            ->addFilter('availableStock', CType\Filter\NumberType::class, [
                'label'    => 'ekyna_commerce.field.available_stock',
                'position' => 60,
            ])
            ->addFilter('orderedStock', CType\Filter\NumberType::class, [
                'label'    => 'ekyna_commerce.supplier_product.field.ordered_stock',
                'position' => 70,
            ])
            ->addFilter('estimatedDateOfArrival', CType\Filter\DateTimeType::class, [
                'label'    => 'ekyna_commerce.field.replenishment_eda',
                'position' => 80,
            ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('subject', null)
            ->setDefault('supplier', null)
            ->setAllowedTypes('subject', ['null', SubjectInterface::class])
            ->setAllowedTypes('supplier', ['null', SupplierInterface::class]);
    }
}
