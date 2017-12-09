<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\CommerceBundle\Model;
use Ekyna\Bundle\CommerceBundle\Table\Column;
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
            ->addColumn('number', BType\Column\AnchorType::class, [
                'label'                => 'ekyna_core.field.number',
                'route_name'           => 'ekyna_commerce_quote_admin_show',
                'route_parameters_map' => [
                    'quoteId' => 'id',
                ],
                'position'             => 10,
            ])
            ->addColumn('voucherNumber', CType\Column\TextType::class, [
                'label'    => 'ekyna_commerce.sale.field.voucher_number',
                'position' => 30,
            ])
            ->addColumn('grandTotal', BType\Column\PriceType::class, [
                'label'         => 'ekyna_commerce.sale.field.grand_total',
                'currency_path' => 'currency.code',
                'position'      => 40,
            ])
            ->addColumn('paidTotal', BType\Column\PriceType::class, [
                'label'         => 'ekyna_commerce.sale.field.paid_total',
                'currency_path' => 'currency.code',
                'position'      => 50,
            ])
            ->addColumn('state', Column\SaleStateType::class, [
                'label'    => 'ekyna_commerce.sale.field.state',
                'position' => 60,
            ])
            ->addColumn('paymentState', Column\PaymentStateType::class, [
                'label'    => 'ekyna_commerce.sale.table.payment_state',
                'position' => 70,
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
            $builder->addColumn('customer', Column\SaleCustomerType::class, [
                'label'    => 'ekyna_commerce.customer.label.singular',
                'position' => 20,
            ]);
        }

        if ($filters) {
            $builder
                ->addFilter('number', CType\Filter\TextType::class, [
                    'label'    => 'ekyna_core.field.number',
                    'position' => 10,
                ])
                ->addFilter('email', CType\Filter\TextType::class, [
                    'label'    => 'ekyna_core.field.email',
                    'position' => 20,
                ])
                ->addFilter('company', CType\Filter\TextType::class, [
                    'label'    => 'ekyna_core.field.company',
                    'position' => 21,
                ])
                ->addFilter('firstName', CType\Filter\TextType::class, [
                    'label'    => 'ekyna_core.field.first_name',
                    'position' => 22,
                ])
                ->addFilter('lastName', CType\Filter\TextType::class, [
                    'label'    => 'ekyna_core.field.last_name',
                    'position' => 23,
                ])
                ->addFilter('voucherNumber', CType\Filter\TextType::class, [
                    'label'    => 'ekyna_commerce.sale.field.voucher_number',
                    'position' => 30,
                ])
                ->addFilter('granTotal', CType\Filter\NumberType::class, [
                    'label'    => 'ekyna_commerce.sale.field.grand_total',
                    'position' => 40,
                ])
                ->addFilter('paidTotal', CType\Filter\NumberType::class, [
                    'label'    => 'ekyna_commerce.sale.field.paid_total',
                    'position' => 50,
                ])
                ->addFilter('state', CType\Filter\ChoiceType::class, [
                    'label'    => 'ekyna_commerce.sale.field.state',
                    'choices'  => Model\OrderStates::getChoices(),
                    'position' => 60,
                ])
                ->addFilter('paymentState', CType\Filter\ChoiceType::class, [
                    'label'    => 'ekyna_commerce.sale.field.payment_state',
                    'choices'  => Model\PaymentStates::getChoices(),
                    'position' => 70,
                ]);
        }
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
