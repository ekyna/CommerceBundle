<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\CmsBundle\Table\Column\TagsType;
use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Bundle\CommerceBundle\Table\Column;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
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
            if (null !== $customer = $options['customer']) {
                $source = $builder->getSource();
                if (!$source instanceof EntitySource) {
                    throw new InvalidArgumentException("Expected instance of " . EntitySource::class);
                }

                $source->setQueryBuilderInitializer(function (QueryBuilder $qb, $alias) use ($customer) {
                    $qb
                        ->join($alias . '.order', 'o')
                        ->andWhere($qb->expr()->eq('o.customer', ':customer'))
                        ->setParameter('customer', $customer);
                });

                $builder
                    ->setFilterable(false)
                    ->setPerPageChoices([100]);
            } else {
                $builder
                    ->setExportable(true)
                    ->setConfigurable(true)
                    ->setProfileable(true)
                    ->addColumn('customer', Column\SaleCustomerType::class, [
                        'label'         => 'ekyna_commerce.customer.label.singular',
                        'property_path' => 'order',
                        'position'      => 25,
                    ])
                    ->addColumn('flags', Column\SaleFlagsType::class, [
                        'property_path' => 'order',
                        'position'      => 14,
                    ])
                    ->addColumn('tags', TagsType::class, [
                        'property_path' => 'order.allTags',
                        'position'      => 998,
                    ])
                    ->addFilter('order', CType\Filter\TextType::class, [
                        'label'         => 'ekyna_commerce.order.label.singular',
                        'property_path' => 'order.number',
                        'position'      => 15,
                    ])
                    ->addFilter('email', CType\Filter\TextType::class, [
                        'label'         => 'ekyna_core.field.email',
                        'property_path' => 'order.email',
                        'position'      => 30,
                    ])
                    ->addFilter('company', CType\Filter\TextType::class, [
                        'label'         => 'ekyna_core.field.company',
                        'property_path' => 'order.company',
                        'position'      => 31,
                    ])
                    ->addFilter('firstName', CType\Filter\TextType::class, [
                        'label'         => 'ekyna_core.field.first_name',
                        'property_path' => 'order.firstName',
                        'position'      => 32,
                    ])
                    ->addFilter('lastName', CType\Filter\TextType::class, [
                        'label'         => 'ekyna_core.field.last_name',
                        'property_path' => 'order.lastName',
                        'position'      => 33,
                    ]);
            }

            $builder
                ->addColumn('order', Column\OrderType::class, [
                    'position' => 15,
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
            ->setDefault('customer', null)
            ->setAllowedTypes('order', ['null', OrderInterface::class])
            ->setAllowedTypes('customer', ['null', CustomerInterface::class]);
    }
}
