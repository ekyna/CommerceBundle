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
use Ekyna\Component\Commerce\Invoice\Model\InvoiceStates;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntitySource;
use Ekyna\Component\Table\Exception\InvalidArgumentException;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;
use Ekyna\Component\Table\View;
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
        $source = $builder->getSource();
        if (!$source instanceof EntitySource) {
            throw new InvalidArgumentException("Expected instance of " . EntitySource::class);
        }

        $filters = [];

        /** @var SubjectInterface $subject */
        $subject = $options['subject'];
        /** @var CustomerInterface $customer */
        $customer = $options['customer'];

        if (null !== $subject) {
            $filters[] = function (QueryBuilder $qb, $alias) use ($subject) {
                $qb
                    ->join($alias . '.items', 'i')
                    ->leftJoin('i.children', 'c')
                    ->leftJoin('c.children', 'sc')
                    ->leftJoin('sc.children', 'ssc')
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
                        ),
                        $qb->expr()->andX(
                            $qb->expr()->eq('ssc.subjectIdentity.provider', ':provider'),
                            $qb->expr()->eq('ssc.subjectIdentity.identifier', ':identifier')
                        )
                    ))
                    ->setParameter('provider', $subject::getProviderName())
                    ->setParameter('identifier', $subject->getId());
            };
        } elseif (null !== $customer) {
            $filters[] = function (QueryBuilder $qb, $alias) use ($customer) {
                if ($customer->hasParent()) {
                    $qb->andWhere($qb->expr()->orX(
                        $qb->expr()->in($alias . '.customer', ':customer'),
                        $qb->expr()->in($alias . '.originCustomer', ':customer')
                    ));
                } else {
                    $qb->andWhere($qb->expr()->eq($alias . '.customer', ':customer'));
                }
                $qb->setParameter('customer', $customer);
            };
        }

        foreach (['state', 'paymentState', 'shipmentState', 'invoiceState'] as $property) {
            if (!empty($states = $options[$property])) {
                $filters[] = function (QueryBuilder $qb, $alias) use ($property, $states) {
                    $qb->andWhere($qb->expr()->in($alias . '.' . $property, $states));
                };
            }
        }

        if (!empty($filters)) {
            $builder
                ->setFilterable(false)
                ->setPerPageChoices([100]);

            $source->setQueryBuilderInitializer(function (QueryBuilder $qb, $alias) use ($filters) {
                foreach ($filters as $filter) {
                    $filter($qb, $alias);
                }
            });
        } else {
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
            ->addColumn('number', Type\Column\OrderType::class, [
                'label'         => 'ekyna_core.field.number',
                'property_path' => false,
                'position'      => 10,
            ])
            ->addColumn('createdAt', CType\Column\DateTimeType::class, [
                'label'       => 'ekyna_core.field.date',
                'position'    => 30,
                'time_format' => 'none',
            ])
            ->addColumn('paidAt', Type\Column\SalePaymentCompletedAtType::class, [
                'position'    => 35,
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
            ->addColumn('originNumber', CType\Column\TextType::class, [
                'label'    => 'ekyna_commerce.sale.field.origin_number',
                'position' => 50,
            ])
            ->addColumn('grandTotal', Type\Column\CurrencyType::class, [
                'label'    => 'ekyna_commerce.sale.field.ati_total',
                'position' => 60,
            ])
            ->addColumn('paidTotal', Type\Column\CurrencyType::class, [
                'label'    => 'ekyna_commerce.sale.field.paid_total',
                'position' => 70,
            ])
            ->addColumn('state', Type\Column\SaleStateType::class, [
                'label'    => 'ekyna_commerce.field.status',
                'position' => 80,
            ])
            ->addColumn('paymentState', Type\Column\PaymentStateType::class, [
                'label'    => 'ekyna_commerce.sale.table.payment_state',
                'position' => 90,
            ])
            ->addColumn('shipmentState', Type\Column\ShipmentStateType::class, [
                'label'    => 'ekyna_commerce.sale.table.shipment_state',
                'position' => 100,
            ])
            ->addColumn('invoiceState', Type\Column\InvoiceStateType::class, [
                'label'    => 'ekyna_commerce.sale.table.invoice_state',
                'position' => 110,
            ])
            /*->addColumn('inCharge', Type\Column\InChargeType::class, [
                'position' => 120,
            ])*/
            /*->addColumn('sample', CType\Column\BooleanType::class, [
                'label'       => 'ekyna_commerce.field.sample',
                'true_class'  => 'label-warning',
                'false_class' => 'label-default',
                'position'    => 130,
            ])*/
            ->addColumn('tags', TagsType::class, [
                'property_path' => 'allTags',
                'position'      => 140,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'buttons' => [
                    [
                        'label'                => 'ekyna_commerce.sale.button.prepare',
                        'icon'                 => 'list',
                        'class'                => 'primary',
                        'route_name'           => 'ekyna_commerce_order_admin_prepare',
                        'route_parameters_map' => [
                            'orderId' => 'id',
                        ],
                        'permission'           => 'edit',
                        'filter'               => function (RowInterface $row) {
                            /** @var OrderInterface $order */
                            $order = $row->getData();

                            return ShipmentStates::isPreparableState($order->getShipmentState());
                        },
                    ],
                    [
                        'label'                => 'ekyna_commerce.sale.button.abort',
                        'icon'                 => 'list',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_commerce_order_admin_abort',
                        'route_parameters_map' => [
                            'orderId' => 'id',
                        ],
                        'permission'           => 'edit',
                        'filter'               => function (RowInterface $row) {
                            /** @var OrderInterface $order */
                            $order = $row->getData();

                            return $order->getShipmentState() === ShipmentStates::STATE_PREPARATION;
                        },
                    ],
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'icon'                 => 'pencil',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_commerce_order_admin_edit',
                        'route_parameters_map' => [
                            'orderId' => 'id',
                        ],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'icon'                 => 'trash',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_commerce_order_admin_remove',
                        'route_parameters_map' => [
                            'orderId' => 'id',
                        ],
                        'permission'           => 'delete',
                        'disable'              => function (RowInterface $row) {
                            /** @var OrderInterface $order */
                            $order = $row->getData();

                            return !OrderStates::isDeletableState($order->getState());
                        },
                    ],
                ],
            ]);

        if (null === $customer) {
            $builder->addColumn('customer', Type\Column\SaleCustomerType::class, [
                'label'    => 'ekyna_commerce.customer.label.singular',
                'position' => 30,
            ]);
        }

        if (!empty($filters)) {
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
            ->addFilter('originNumber', CType\Filter\TextType::class, [
                'label'    => 'ekyna_commerce.sale.field.origin_number',
                'position' => 50,
            ])
            ->addFilter('grandTotal', CType\Filter\NumberType::class, [
                'label'    => 'ekyna_commerce.sale.field.ati_total',
                'position' => 60,
            ])
            ->addFilter('paidTotal', CType\Filter\NumberType::class, [
                'label'    => 'ekyna_commerce.sale.field.paid_total',
                'position' => 70,
            ])
            ->addFilter('state', CType\Filter\ChoiceType::class, [
                'label'    => 'ekyna_commerce.field.status',
                'choices'  => Model\OrderStates::getChoices(),
                'position' => 80,
            ])
            ->addFilter('paymentMethod', ResourceType::class, [
                'resource' => 'ekyna_commerce.payment_method',
                'position' => 90,
            ])
            ->addFilter('paymentState', CType\Filter\ChoiceType::class, [
                'label'    => 'ekyna_commerce.sale.field.payment_state',
                'choices'  => Model\PaymentStates::getChoices([
                    PaymentStates::STATE_EXPIRED,
                    PaymentStates::STATE_SUSPENDED,
                    PaymentStates::STATE_UNKNOWN,
                ]),
                'position' => 100,
            ])
            ->addFilter('shipmentState', CType\Filter\ChoiceType::class, [
                'label'    => 'ekyna_commerce.sale.field.shipment_state',
                'choices'  => Model\ShipmentStates::getChoices([
                    //ShipmentStates::STATE_NEW,
                    ShipmentStates::STATE_SHIPPED,
                    ShipmentStates::STATE_NONE,
                ]),
                'position' => 110,
            ])
            ->addFilter('invoiceState', CType\Filter\ChoiceType::class, [
                'label'    => 'ekyna_commerce.sale.field.invoice_state',
                'choices'  => Model\InvoiceStates::getChoices([
                    //InvoiceStates::STATE_NEW,
                    InvoiceStates::STATE_INVOICED,
                ]),
                'position' => 120,
            ])
            /*->addFilter('inCharge', Type\Filter\InChargeType::class, [
                'position' => 120,
            ])*/
            ->addFilter('sample', CType\Filter\BooleanType::class, [
                'label'    => 'ekyna_commerce.field.sample',
                'position' => 130,
            ])
            ->addFilter('tags', Type\Filter\SaleTagsType::class, [
                'label'    => 'ekyna_cms.tag.label.plural',
                'position' => 140,
            ])
            ->addFilter('inCharge', Type\Filter\InChargeType::class, [
                'position' => 145,
            ])
            ->addFilter('subject', Type\Filter\SaleSubjectType::class, [
                'label'    => 'Article',
                'position' => 150,
            ]);

        $builder
            ->addAction('prepare', Type\Action\OrderPrepareActionType::class)
            ->addAction('abort', Type\Action\OrderAbortActionType::class);
    }

    /**
     * @inheritDoc
     */
    public function buildRowView(View\RowView $view, RowInterface $row, array $options)
    {
        /** @var OrderInterface $order */
        $order = $row->getData();

        if ($order->isFirst()) {
            $view->vars['attr']['class'] = 'success';
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
            ->setDefault('state', [])
            ->setDefault('shipmentState', [])
            ->setDefault('paymentState', [])
            ->setDefault('invoiceState', [])
            ->setAllowedTypes('subject', ['null', SubjectInterface::class])
            ->setAllowedTypes('customer', ['null', CustomerInterface::class])
            ->setAllowedTypes('state', 'array')
            ->setAllowedTypes('shipmentState', 'array')
            ->setAllowedTypes('paymentState', 'array')
            ->setAllowedTypes('invoiceState', 'array');
    }
}
