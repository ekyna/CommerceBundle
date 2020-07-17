<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\CmsBundle\Table\Column\TagsType;
use Ekyna\Bundle\CommerceBundle\Model;
use Ekyna\Bundle\CommerceBundle\Table as Type;
use Ekyna\Bundle\ResourceBundle\Table\Filter\ResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntitySource;
use Ekyna\Component\Table\Exception\InvalidArgumentException;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class QuoteType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteType extends ResourceTableType
{
    /**
     * {@inheritdoc}
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $filters = false;
        /** @var CustomerInterface $customer */
        if (null !== $customer = $options['customer']) {
            $source = $builder->getSource();
            if (!$source instanceof EntitySource) {
                throw new InvalidArgumentException("Expected instance of " . EntitySource::class);
            }

            $source->setQueryBuilderInitializer(function (QueryBuilder $qb, $alias) use ($customer) {
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
            ->addColumn('flags', Type\Column\SaleFlagsType::class, [
                'property_path' => false,
                'position'      => 5,
            ])
            ->addColumn('number', Type\Column\QuoteType::class, [
                'label'         => 'ekyna_core.field.number',
                'property_path' => false,
                'position'      => 10,
            ])
            ->addColumn('createdAt', CType\Column\DateTimeType::class, [
                'label'       => 'ekyna_core.field.date',
                'position'    => 20,
                'time_format' => 'none',
            ])
            ->addColumn('title', CType\Column\TextType::class, [
                'label'    => 'ekyna_core.field.title',
                'position' => 40,
            ])
            ->addColumn('voucherNumber', CType\Column\TextType::class, [
                'label'    => 'ekyna_commerce.sale.field.voucher_number',
                'position' => 45,
            ])
            ->addColumn('grandTotal', Type\Column\CurrencyType::class, [
                'label'    => 'ekyna_commerce.sale.field.ati_total',
                'position' => 50,
            ])
            ->addColumn('paidTotal', Type\Column\CurrencyType::class, [
                'label'    => 'ekyna_commerce.sale.field.paid_total',
                'position' => 60,
            ])
            ->addColumn('state', Type\Column\SaleStateType::class, [
                'label'    => 'ekyna_commerce.field.status',
                'position' => 70,
            ])
            ->addColumn('paymentState', Type\Column\PaymentStateType::class, [
                'label'    => 'ekyna_commerce.sale.table.payment_state',
                'position' => 80,
            ])
            /*->addColumn('inCharge', Type\Column\InChargeType::class, [
                'position' => 90,
            ])*/
            ->addColumn('tags', TagsType::class, [
                'property_path' => 'allTags',
                'position'      => 100,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_commerce_quote_admin_edit',
                        'route_parameters_map' => [
                            'quoteId' => 'id',
                        ],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_commerce_quote_admin_remove',
                        'route_parameters_map' => [
                            'quoteId' => 'id',
                        ],
                        'permission'           => 'delete',
                    ],
                ],
            ]);

        if (null === $customer || $customer->hasChildren()) {
            $builder->addColumn('customer', Type\Column\SaleCustomerType::class, [
                'label'    => 'ekyna_commerce.customer.label.singular',
                'position' => 30,
            ]);
        }

        if (!$filters) {
            return;
        }
        $builder
            ->addFilter('number', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.number',
                'position' => 10,
            ])
            ->addFilter('createdAt', CType\Filter\DateTimeType::class, [
                'label'    => 'ekyna_core.field.created_at',
                'position' => 20,
                'time'     => false,
            ])
            ->addFilter('email', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.email',
                'position' => 30,
            ])
            ->addFilter('company', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.company',
                'position' => 31,
            ])
            ->addFilter('firstName', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.first_name',
                'position' => 32,
            ])
            ->addFilter('lastName', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.last_name',
                'position' => 33,
            ])
            ->addFilter('companyNumber', CType\Filter\TextType::class, [
                'label'         => 'ekyna_commerce.customer.field.company_number',
                'property_path' => 'customer.companyNumber',
                'position'      => 34,
            ])
            ->addFilter('customerGroup', ResourceType::class, [
                'resource' => 'ekyna_commerce.customer_group',
                'position' => 35,
            ])
            ->addFilter('title', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.title',
                'position' => 40,
            ])
            ->addFilter('voucherNumber', CType\Filter\TextType::class, [
                'label'    => 'ekyna_commerce.sale.field.voucher_number',
                'position' => 45,
            ])
            ->addFilter('grandTotal', CType\Filter\NumberType::class, [
                'label'    => 'ekyna_commerce.sale.field.ati_total',
                'position' => 50,
            ])
            ->addFilter('paidTotal', CType\Filter\NumberType::class, [
                'label'    => 'ekyna_commerce.sale.field.paid_total',
                'position' => 60,
            ])
            ->addFilter('state', CType\Filter\ChoiceType::class, [
                'label'    => 'ekyna_commerce.field.status',
                'choices'  => Model\OrderStates::getChoices(),
                'position' => 70,
            ])
            ->addFilter('paymentState', CType\Filter\ChoiceType::class, [
                'label'    => 'ekyna_commerce.sale.field.payment_state',
                'choices'  => Model\PaymentStates::getChoices(),
                'position' => 80,
            ])
            /*->addFilter('inCharge', Type\Filter\InChargeType::class, [
                'position' => 90,
            ])*/
            ->addFilter('tags', Type\Filter\SaleTagsType::class, [
                'label'    => 'ekyna_cms.tag.label.plural',
                'position' => 100,
            ])
            ->addFilter('subject', Type\Filter\SaleSubjectType::class, [
                'label'    => 'Article',
                'position' => 150,
            ]);

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('customer', null)
            ->setAllowedTypes('customer', ['null', CustomerInterface::class]);
    }
}
