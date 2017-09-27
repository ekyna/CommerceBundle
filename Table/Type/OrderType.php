<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\CmsBundle\Table\Column\TagsType;
use Ekyna\Bundle\CommerceBundle\Model;
use Ekyna\Bundle\CommerceBundle\Table as Type;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntitySource;
use Ekyna\Component\Table\Exception\InvalidArgumentException;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OrderType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderType extends ResourceTableType
{
    /**
     * {@inheritdoc}
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $filters = false;
        if (null !== $subject = $options['subject']) {
            $source = $builder->getSource();
            if (!$source instanceof EntitySource) {
                throw new InvalidArgumentException("Expected instance of " . EntitySource::class);
            }

            $source->setQueryBuilderInitializer(function (QueryBuilder $qb, $alias) use ($subject) {
                $qb
                    ->join($alias . '.items', 'i')
                    ->leftJoin('i.children', 'c')
                    ->leftJoin('c.children', 'sc')
                    ->andWhere($qb->expr()->orX(
                        $qb->expr()->andX(
                            $qb->expr()->eq('i.subjectIdentity.provider', ':provider'),
                            $qb->expr()->eq('i.subjectIdentity.identifier', ':identifier')
                        ),
                        $qb->expr()->andX(
                            $qb->expr()->eq('c.subjectIdentity.provider', ':provider'),
                            $qb->expr()->eq('c.subjectIdentity.identifier', ':identifier')
                        ),
                        $qb->expr()->andX(
                            $qb->expr()->eq('sc.subjectIdentity.provider', ':provider'),
                            $qb->expr()->eq('sc.subjectIdentity.identifier', ':identifier')
                        )
                    ))
                    ->setParameter('provider', $subject::getProviderName())
                    ->setParameter('identifier', $subject->getId());
            });

            $builder->setFilterable(false);
        } elseif (null !== $customer = $options['customer']) {
            $source = $builder->getSource();
            if (!$source instanceof EntitySource) {
                throw new InvalidArgumentException("Expected instance of " . EntitySource::class);
            }

            $source->setQueryBuilderInitializer(function (QueryBuilder $qb, $alias) use ($customer) {
                $qb
                    ->andWhere($qb->expr()->eq($alias . '.customer', ':customer'))
                    ->setParameter('customer', $customer);
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
                'route_name'           => 'ekyna_commerce_order_admin_show',
                'route_parameters_map' => [
                    'orderId' => 'id',
                ],
                'position'             => 10,
            ])
            ->addColumn('voucherNumber', CType\Column\TextType::class, [
                'label'    => 'ekyna_commerce.sale.field.voucher_number',
                'position' => 30,
            ])
            ->addColumn('originNumber', CType\Column\TextType::class, [
                'label'    => 'ekyna_commerce.sale.field.origin_number',
                'position' => 40,
            ])
            ->addColumn('grandTotal', BType\Column\PriceType::class, [
                'label'         => 'ekyna_commerce.sale.field.grand_total',
                'currency_path' => 'currency.code',
                'position'      => 50,
            ])
            ->addColumn('paidTotal', BType\Column\PriceType::class, [
                'label'         => 'ekyna_commerce.sale.field.paid_total',
                'currency_path' => 'currency.code',
                'position'      => 60,
            ])
            ->addColumn('state', Type\Column\SaleStateType::class, [
                'label'    => 'ekyna_commerce.sale.field.state',
                'position' => 70,
            ])
            ->addColumn('paymentState', Type\Column\PaymentStateType::class, [
                'label'    => 'ekyna_commerce.sale.field.payment_state',
                'position' => 80,
            ])
            ->addColumn('shipmentState', Type\Column\ShipmentStateType::class, [
                'label'    => 'ekyna_commerce.sale.field.shipment_state',
                'position' => 90,
            ])
            ->addColumn('inCharge', Type\Column\InChargeType::class, [
                'position' => 100,
            ])
            ->addColumn('tags', TagsType::class, [
                'property_path' => 'allTags',
                'position'      => 110,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_commerce_order_admin_edit',
                        'route_parameters_map' => [
                            'orderId' => 'id',
                        ],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_commerce_order_admin_remove',
                        'route_parameters_map' => [
                            'orderId' => 'id',
                        ],
                        'permission'           => 'delete',
                    ],
                ],
            ]);

        if ($filters) {
            $builder
                ->addFilter('number', CType\Filter\TextType::class, [
                    'label'    => 'ekyna_core.field.number',
                    'position' => 10,
                ])
                ->addFilter('voucherNumber', CType\Filter\TextType::class, [
                    'label'    => 'ekyna_commerce.sale.field.voucher_number',
                    'position' => 30,
                ])
                ->addFilter('originNumber', CType\Filter\TextType::class, [
                    'label'    => 'ekyna_commerce.sale.field.origin_number',
                    'position' => 40,
                ])
                ->addFilter('granTotal', CType\Filter\NumberType::class, [
                    'label'    => 'ekyna_commerce.sale.field.grand_total',
                    'position' => 50,
                ])
                ->addFilter('paidTotal', CType\Filter\NumberType::class, [
                    'label'    => 'ekyna_commerce.sale.field.paid_total',
                    'position' => 60,
                ])
                ->addFilter('state', CType\Filter\ChoiceType::class, [
                    'label'    => 'ekyna_commerce.sale.field.state',
                    'choices'  => Model\OrderStates::getChoices(),
                    'position' => 70,
                ])
                ->addFilter('paymentState', CType\Filter\ChoiceType::class, [
                    'label'    => 'ekyna_commerce.sale.field.payment_state',
                    'choices'  => Model\PaymentStates::getChoices(),
                    'position' => 80,
                ])
                ->addFilter('shipmentState', CType\Filter\ChoiceType::class, [
                    'label'    => 'ekyna_commerce.sale.field.shipment_state',
                    'choices'  => Model\ShipmentStates::getChoices(),
                    'position' => 90,
                ])
                ->addFilter('inCharge', Type\Filter\InChargeType::class, [
                    'position' => 100,
                ])
                ->addFilter('tags', Type\Filter\OrderTagsType::class, [
                    'label'    => 'ekyna_cms.tag.label.plural',
                    'position' => 110,
                ]);
        }

        if (null === $options['customer']) {
            $builder
                ->addColumn('customer', Type\Column\SaleCustomerType::class, [
                    'label'    => 'ekyna_commerce.customer.label.singular',
                    'position' => 20,
                ]);

            if ($filters) {
                $builder
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
                    ]);
            }
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
            ->setDefault('subject', null)
            ->setDefault('states', [])
            ->setAllowedTypes('subject', ['null', SubjectInterface::class])
            ->setAllowedTypes('customer', ['null', CustomerInterface::class])
            ->setAllowedTypes('states', 'array');
    }
}
