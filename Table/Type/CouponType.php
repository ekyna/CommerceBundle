<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\CommerceBundle\Model\AdjustmentModes;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntitySource;
use Ekyna\Component\Table\Exception\InvalidArgumentException;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CouponType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CouponType extends ResourceTableType
{
    /**
     * {@inheritdoc}
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $source = $builder->getSource();
        if (!$source instanceof EntitySource) {
            throw new InvalidArgumentException("Expected instance of " . EntitySource::class);
        }

        if ($customer = $options['customer']) {
            $builder
                ->setFilterable(false)
                ->setSortable(false)
                ->setBatchable(false)
                ->setConfigurable(false)
                ->setExportable(false)
                ->setProfileable(false);

            $initializer = function (QueryBuilder $qb, $alias) use ($customer) {
                $qb
                    ->andWhere($qb->expr()->eq($alias . '.customer', ':customer'))
                    ->setParameter('customer', $customer);
            };
        } else {
            $initializer = function (QueryBuilder $qb, $alias) {
                $qb->andWhere($qb->expr()->isNull($alias . '.customer'));
            };
        }

        $source->setQueryBuilderInitializer($initializer);

        $builder
            ->addColumn('code', BType\Column\AnchorType::class, [
                'label'                => 'ekyna_core.field.code',
                'sortable'             => true,
                'route_name'           => 'ekyna_commerce_coupon_admin_show',
                'route_parameters_map' => ['couponId' => 'id'],
                'position'             => 10,
            ])
            // TODO usage column
            ->addColumn('usage', CType\Column\NumberType::class, [
                'label'     => 'ekyna_commerce.coupon.field.usage',
                'precision' => 0,
                'sortable'  => true,
                'position'  => 20,
            ])
            ->addColumn('limit', CType\Column\NumberType::class, [
                'label'     => 'ekyna_commerce.coupon.field.limit',
                'precision' => 0,
                'sortable'  => true,
                'position'  => 30,
            ])
            ->addColumn('mode', CType\Column\ChoiceType::class, [
                'label'    => 'ekyna_core.field.mode',
                'choices'  => AdjustmentModes::getChoices(),
                'sortable' => true,
                'position' => 40,
            ])
            ->addColumn('amount', CType\Column\NumberType::class, [
                // TODO format regarding to mode
                'label'    => 'ekyna_core.field.amount',
                'sortable' => true,
                'position' => 50,
            ])
            ->addColumn('startAt', CType\Column\DateTimeType::class, [
                'label'       => 'ekyna_core.field.from_date',
                'time_format' => 'none',
                'sortable'    => true,
                'position'    => 60,
            ])
            ->addColumn('endAt', CType\Column\DateTimeType::class, [
                'label'       => 'ekyna_core.field.from_date',
                'time_format' => 'none',
                'sortable'    => true,
                'position'    => 70,
            ])
            ->addColumn('designation', CType\Column\TextType::class, [
                'label'    => 'ekyna_core.field.designation',
                'sortable' => true,
                'position' => 80,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_commerce_coupon_admin_edit',
                        'route_parameters_map' => ['couponId' => 'id'],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_commerce_coupon_admin_remove',
                        'route_parameters_map' => ['couponId' => 'id'],
                        'permission'           => 'delete',
                    ],
                ],
            ])
            ->addFilter('code', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.code',
                'position' => 10,
            ])
            ->addFilter('usage', CType\Filter\NumberType::class, [
                'label'    => 'ekyna_commerce.coupon.field.usage',
                'position' => 20,
            ])
            ->addFilter('limit', CType\Filter\NumberType::class, [
                'label'    => 'ekyna_commerce.coupon.field.limit',
                'position' => 30,
            ])
            ->addFilter('mode', CType\Filter\ChoiceType::class, [
                'label'    => 'ekyna_core.field.mode',
                'choices'  => AdjustmentModes::getChoices(),
                'position' => 40,
            ])
            ->addFilter('amount', CType\Filter\NumberType::class, [
                'label'    => 'ekyna_core.field.value',
                'position' => 50,
            ])
            ->addFilter('startAt', CType\Filter\DateTimeType::class, [
                'label'    => 'ekyna_core.field.from_date',
                'time'     => false,
                'position' => 60,
            ])
            ->addFilter('endAt', CType\Filter\DateTimeType::class, [
                'label'    => 'ekyna_core.field.to_date',
                'time'     => false,
                'position' => 70,
            ])
            ->addFilter('designation', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.designation',
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
            ->setDefault('customer', null)
            ->setAllowedTypes('customer', [CustomerInterface::class, 'null']);
    }
}
