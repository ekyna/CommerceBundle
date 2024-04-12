<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Table\Type as AType;
use Ekyna\Bundle\CommerceBundle\Model\AdjustmentModes;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntitySource;
use Ekyna\Component\Table\Exception\UnexpectedTypeException;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class CouponType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CouponType extends AbstractResourceType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $source = $builder->getSource();
        if (!$source instanceof EntitySource) {
            throw new UnexpectedTypeException($source, EntitySource::class);
        }

        if ($customer = $options['customer']) {
            $builder
                ->setFilterable(false)
                ->setSortable(false)
                ->setBatchable(false);

            $initializer = function (QueryBuilder $qb, string $alias) use ($customer): void {
                $qb
                    ->andWhere($qb->expr()->eq($alias . '.customer', ':customer'))
                    ->setParameter('customer', $customer);
            };
        } else {
            $initializer = function (QueryBuilder $qb, string $alias): void {
                $qb->andWhere($qb->expr()->isNull($alias . '.customer'));
            };
        }

        $source->setQueryBuilderInitializer($initializer);

        $builder
            ->addColumn('code', BType\Column\AnchorType::class, [
                'label'    => t('field.code', [], 'EkynaUi'),
                'sortable' => true,
                'position' => 10,
            ])
            // TODO usage column
            ->addColumn('usage', CType\Column\NumberType::class, [
                'label'     => t('coupon.field.usage', [], 'EkynaCommerce'),
                'precision' => 0,
                'sortable'  => true,
                'position'  => 20,
            ])
            ->addColumn('limit', CType\Column\NumberType::class, [
                'label'     => t('coupon.field.limit', [], 'EkynaCommerce'),
                'precision' => 0,
                'sortable'  => true,
                'position'  => 30,
            ])
            ->addColumn('mode', AType\Column\ConstantChoiceType::class, [
                'label'    => t('field.mode', [], 'EkynaUi'),
                'class'    => AdjustmentModes::class,
                'sortable' => true,
                'position' => 40,
            ])
            ->addColumn('amount', CType\Column\NumberType::class, [
                // TODO format regarding to mode
                'label'    => t('field.amount', [], 'EkynaUi'),
                'sortable' => true,
                'position' => 50,
            ])
            ->addColumn('startAt', CType\Column\DateTimeType::class, [
                'label'       => t('field.from_date', [], 'EkynaUi'),
                'time_format' => 'none',
                'sortable'    => true,
                'position'    => 60,
            ])
            ->addColumn('endAt', CType\Column\DateTimeType::class, [
                'label'       => t('field.from_date', [], 'EkynaUi'),
                'time_format' => 'none',
                'sortable'    => true,
                'position'    => 70,
            ])
            ->addColumn('designation', CType\Column\TextType::class, [
                'label'    => t('field.designation', [], 'EkynaUi'),
                'sortable' => true,
                'position' => 80,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'resource' => $this->dataClass,
            ])
            ->addFilter('code', CType\Filter\TextType::class, [
                'label'    => t('field.code', [], 'EkynaUi'),
                'position' => 10,
            ])
            ->addFilter('usage', CType\Filter\NumberType::class, [
                'label'    => t('coupon.field.usage', [], 'EkynaCommerce'),
                'position' => 20,
            ])
            ->addFilter('limit', CType\Filter\NumberType::class, [
                'label'    => t('coupon.field.limit', [], 'EkynaCommerce'),
                'position' => 30,
            ])
            ->addFilter('mode', AType\Filter\ConstantChoiceType::class, [
                'label'    => t('field.mode', [], 'EkynaUi'),
                'class'    => AdjustmentModes::class,
                'position' => 40,
            ])
            ->addFilter('amount', CType\Filter\NumberType::class, [
                'label'    => t('field.value', [], 'EkynaUi'),
                'position' => 50,
            ])
            ->addFilter('startAt', CType\Filter\DateTimeType::class, [
                'label'    => t('field.from_date', [], 'EkynaUi'),
                'time'     => false,
                'position' => 60,
            ])
            ->addFilter('endAt', CType\Filter\DateTimeType::class, [
                'label'    => t('field.to_date', [], 'EkynaUi'),
                'time'     => false,
                'position' => 70,
            ])
            ->addFilter('designation', CType\Filter\TextType::class, [
                'label'    => t('field.designation', [], 'EkynaUi'),
                'position' => 80,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('customer', null)
            ->setAllowedTypes('customer', [CustomerInterface::class, 'null']);
    }
}
