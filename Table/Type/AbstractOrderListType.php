<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntitySource;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OrderListType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractOrderListType extends ResourceTableType
{
    /**
     * @inheritDoc
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        if (null !== $order = $options['order']) {
            $source = $builder->getSource();
            if (!$source instanceof EntitySource) {
                throw new InvalidArgumentException("Expected instance of " . EntitySource::class);
            }

            $source->setQueryBuilderInitializer(function (QueryBuilder $qb, $alias) use ($order) {
                $qb
                    ->andWhere($qb->expr()->eq($alias . '.order', ':order'))
                    ->setParameter('order', $order);
            });

            $builder->setFilterable(false);
        } else {
            $builder
                ->setExportable(true)
                ->setConfigurable(true)
                ->setProfileable(true)
                ->addColumn('order', BType\Column\AnchorType::class, [
                    'label'                => 'ekyna_commerce.order.label.singular',
                    'property_path'        => 'order.number',
                    'route_name'           => 'ekyna_commerce_order_admin_show',
                    'route_parameters_map' => [
                        'orderId' => 'order.id',
                    ],
                    'position'             => 15,
                ])
                ->addFilter('order', CType\Filter\TextType::class, [
                    'label'         => 'ekyna_commerce.order.label.singular',
                    'property_path' => 'order.number',
                    'position'      => 15,
                ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('order', null)
            ->setAllowedTypes('order', ['null', OrderInterface::class]);
    }
}
