<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Table as Type;
use Ekyna\Bundle\ResourceBundle\Table\Filter\ResourceType;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntitySource;
use Ekyna\Component\Table\Exception\UnexpectedTypeException;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_merge;
use function Symfony\Component\Translation\t;

/**
 * Class CartType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartType extends AbstractResourceType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $filters = false;
        /** @var CustomerInterface $customer */
        if (null !== $customer = $options['customer']) {
            $source = $builder->getSource();
            if (!$source instanceof EntitySource) {
                throw new UnexpectedTypeException($source, EntitySource::class);
            }

            $source->setQueryBuilderInitializer(function (QueryBuilder $qb, string $alias) use ($customer): void {
                if ($customer->hasChildren()) {
                    $qb
                        ->andWhere($qb->expr()->in($alias . '.customer', ':customers'))
                        ->setParameter('customers', array_merge([$customer], $customer->getChildren()->toArray()));
                } else {
                    $qb
                        ->andWhere($qb->expr()->eq($alias . '.customer', ':customer'))
                        ->setParameter('customer', $customer);
                }
            });

            $builder->setFilterable(false);
        } else {
            $filters = true;
            $builder
                ->setExportable(true)
                ->setConfigurable(true)
                ->setProfileable(true);
        }

        $builder
            ->addDefaultSort('createdAt', ColumnSort::DESC)
            ->addColumn('number', Type\Column\CartType::class, [
                'label'         => t('field.number', [], 'EkynaUi'),
                'property_path' => false,
                'position'      => 10,
            ])
            ->addColumn('createdAt', CType\Column\DateTimeType::class, [
                'label'       => t('field.date', [], 'EkynaUi'),
                'position'    => 20,
                'time_format' => 'none',
            ])
            ->addColumn('grandTotal', Type\Column\CurrencyType::class, [
                'label'    => t('sale.field.ati_total', [], 'EkynaCommerce'),
                'position' => 50,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'resource' => $this->dataClass,
            ]);

        if (null === $customer || $customer->hasChildren()) {
            $builder->addColumn('customer', Type\Column\SaleCustomerType::class, [
                'label'    => t('customer.label.singular', [], 'EkynaCommerce'),
                'position' => 30,
            ]);
        }

        if ($filters) {
            $builder
                ->addFilter('number', CType\Filter\TextType::class, [
                    'label'    => t('field.number', [], 'EkynaUi'),
                    'position' => 10,
                ])
                ->addFilter('createdAt', CType\Filter\DateTimeType::class, [
                    'label'    => t('field.created_at', [], 'EkynaUi'),
                    'position' => 20,
                    'time'     => false,
                ])
                ->addFilter('customer', Type\Filter\CustomerType::class, [
                    'position' => 30,
                ])
                ->addFilter('email', CType\Filter\TextType::class, [
                    'label'    => t('field.email', [], 'EkynaUi'),
                    'position' => 31,
                ])
                ->addFilter('company', CType\Filter\TextType::class, [
                    'label'    => t('field.company', [], 'EkynaUi'),
                    'position' => 32,
                ])
                ->addFilter('firstName', CType\Filter\TextType::class, [
                    'label'    => t('field.first_name', [], 'EkynaUi'),
                    'position' => 33,
                ])
                ->addFilter('lastName', CType\Filter\TextType::class, [
                    'label'    => t('field.last_name', [], 'EkynaUi'),
                    'position' => 34,
                ])
                ->addFilter('companyNumber', CType\Filter\TextType::class, [
                    'label'         => t('customer.field.company_number', [], 'EkynaCommerce'),
                    'property_path' => 'customer.companyNumber',
                    'position'      => 35,
                ])
                ->addFilter('customerGroup', ResourceType::class, [
                    'resource' => 'ekyna_commerce.customer_group',
                    'position' => 36,
                ])
                ->addFilter('subject', Type\Filter\SaleSubjectType::class, [
                    'position' => 150,
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('customer', null)
            ->setAllowedTypes('customer', ['null', CustomerInterface::class]);
    }
}
