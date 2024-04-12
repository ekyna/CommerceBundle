<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\CmsBundle\Table\Column\TagsType;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Bundle\CommerceBundle\Table\Column;
use Ekyna\Bundle\CommerceBundle\Table\Filter;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntitySource;
use Ekyna\Component\Table\Exception\UnexpectedTypeException;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class OrderListType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractOrderListType extends AbstractResourceType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        if (null !== $order = $options['order']) {
            $source = $builder->getSource();
            if (!$source instanceof EntitySource) {
                throw new UnexpectedTypeException($source, EntitySource::class);
            }

            $source->setQueryBuilderInitializer(function (QueryBuilder $qb, string $alias) use ($order): void {
                $qb
                    ->andWhere($qb->expr()->eq($alias . '.order', ':order'))
                    ->setParameter('order', $order);
            });

            $builder->setFilterable(false);
        } else {
            if (null !== $customer = $options['customer']) {
                $source = $builder->getSource();
                if (!$source instanceof EntitySource) {
                    throw new UnexpectedTypeException($source, EntitySource::class);
                }

                $source->setQueryBuilderInitializer(function (QueryBuilder $qb, string $alias) use ($customer): void {
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
                    ->setConfigurable(true)
                    ->setProfileable(true)
                    ->addColumn('customer', Column\SaleCustomerType::class, [
                        'label'         => t('customer.label.singular', [], 'EkynaCommerce'),
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
                        'label'         => t('order.label.singular', [], 'EkynaCommerce'),
                        'property_path' => 'order.number',
                        'position'      => 15,
                    ])
                    ->addFilter('customer', Filter\CustomerType::class, [
                        'property_path' => 'order.customer',
                        'position'      => 30,
                    ])
                    ->addFilter('email', CType\Filter\TextType::class, [
                        'label'         => t('field.email', [], 'EkynaUi'),
                        'property_path' => 'order.email',
                        'position'      => 31,
                    ])
                    ->addFilter('company', CType\Filter\TextType::class, [
                        'label'         => t('field.company', [], 'EkynaUi'),
                        'property_path' => 'order.company',
                        'position'      => 32,
                    ])
                    ->addFilter('firstName', CType\Filter\TextType::class, [
                        'label'         => t('field.first_name', [], 'EkynaUi'),
                        'property_path' => 'order.firstName',
                        'position'      => 33,
                    ])
                    ->addFilter('lastName', CType\Filter\TextType::class, [
                        'label'         => t('field.last_name', [], 'EkynaUi'),
                        'property_path' => 'order.lastName',
                        'position'      => 34,
                    ])
                    ->addFilter('companyNumber', CType\Filter\TextType::class, [
                        'label'         => t('customer.field.company_number', [], 'EkynaCommerce'),
                        'property_path' => 'order.customer.companyNumber',
                        'position'      => 35,
                    ]);
            }

            $builder
                ->addColumn('order', Column\OrderType::class, [
                    'position' => 15,
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('order', null)
            ->setDefault('customer', null)
            ->setAllowedTypes('order', ['null', OrderInterface::class])
            ->setAllowedTypes('customer', ['null', CustomerInterface::class]);
    }
}
