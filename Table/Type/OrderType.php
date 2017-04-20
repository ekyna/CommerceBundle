<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Action\DeleteAction;
use Ekyna\Bundle\AdminBundle\Action\UpdateAction;
use Ekyna\Bundle\CmsBundle\Table\Column\TagsType;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Order\AbortAction;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Order\PrepareAction;
use Ekyna\Bundle\CommerceBundle\Model;
use Ekyna\Bundle\CommerceBundle\Table as Type;
use Ekyna\Bundle\ResourceBundle\Table\Filter\ResourceType;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceStates;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntitySource;
use Ekyna\Component\Table\Exception\UnexpectedTypeException;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;
use Ekyna\Component\Table\View;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class OrderType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderType extends AbstractResourceType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $source = $builder->getSource();
        if (!$source instanceof EntitySource) {
            throw new UnexpectedTypeException($source, EntitySource::class);
        }

        $filters = [];

        /** @var SubjectInterface $subject */
        $subject = $options['subject'];
        /** @var CustomerInterface $customer */
        $customer = $options['customer'];

        if (null !== $subject) {
            $filters[] = function (QueryBuilder $qb, string $alias) use ($subject): void {
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
            $filters[] = function (QueryBuilder $qb, string $alias) use ($customer): void {
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
                $filters[] = function (QueryBuilder $qb, string $alias) use ($property, $states): void {
                    $qb->andWhere($qb->expr()->in($alias . '.' . $property, $states));
                };
            }
        }

        if (!empty($filters)) {
            $builder
                ->setFilterable(false)
                ->setPerPageChoices([100]);

            $source->setQueryBuilderInitializer(function (QueryBuilder $qb, string $alias) use ($filters): void {
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
                'label'         => t('field.number', [], 'EkynaUi'),
                'property_path' => false,
                'position'      => 10,
            ])
            ->addColumn('createdAt', CType\Column\DateTimeType::class, [
                'label'       => t('field.date', [], 'EkynaUi'),
                'position'    => 30,
                'time_format' => 'none',
            ])
            ->addColumn('paidAt', Type\Column\SalePaymentCompletedAtType::class, [
                'position'    => 35,
                'time_format' => 'none',
            ])
            ->addColumn('title', CType\Column\TextType::class, [
                'label'    => t('field.title', [], 'EkynaUi'),
                'position' => 40,
            ])
            ->addColumn('voucherNumber', CType\Column\TextType::class, [
                'label'    => t('sale.field.voucher_number', [], 'EkynaCommerce'),
                'position' => 45,
            ])
            ->addColumn('originNumber', CType\Column\TextType::class, [
                'label'    => t('sale.field.origin_number', [], 'EkynaCommerce'),
                'position' => 50,
            ])
            ->addColumn('grandTotal', Type\Column\CurrencyType::class, [
                'label'    => t('sale.field.ati_total', [], 'EkynaCommerce'),
                'position' => 60,
            ])
            ->addColumn('paidTotal', Type\Column\CurrencyType::class, [
                'label'    => t('sale.field.paid_total', [], 'EkynaCommerce'),
                'position' => 70,
            ])
            ->addColumn('state', Type\Column\SaleStateType::class, [
                'position' => 80,
            ])
            ->addColumn('paymentState', Type\Column\PaymentStateType::class, [
                'position' => 90,
            ])
            ->addColumn('shipmentState', Type\Column\ShipmentStateType::class, [
                'position' => 100,
            ])
            ->addColumn('invoiceState', Type\Column\InvoiceStateType::class, [
                'position' => 110,
            ])
            /*->addColumn('inCharge', Type\Column\InChargeType::class, [
                'position' => 120,
            ])*/
            /*->addColumn('sample', CType\Column\BooleanType::class, [
                'label'       => t('field.sample', [], 'EkynaCommerce'),
                'true_class'  => 'label-warning',
                'false_class' => 'label-default',
                'position'    => 130,
            ])*/
            ->addColumn('tags', TagsType::class, [
                'property_path' => 'allTags',
                'position'      => 140,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'resource' => $this->dataClass,
                'actions'  => [
                    PrepareAction::class => [
                        'filter' => function (RowInterface $row) {
                            /** @var OrderInterface $order */
                            $order = $row->getData(null);

                            return $order->hasItems()
                                && ShipmentStates::isPreparableState($order->getShipmentState());
                        },
                    ],
                    AbortAction::class   => [
                        'filter' => function (RowInterface $row) {
                            /** @var OrderInterface $order */
                            $order = $row->getData(null);

                            return $order->getShipmentState() === ShipmentStates::STATE_PREPARATION;
                        },
                    ],
                    UpdateAction::class,
                    DeleteAction::class  => [
                        'disable' => function (RowInterface $row) {
                            /** @var OrderInterface $order */
                            $order = $row->getData(null);

                            return !OrderStates::isDeletableState($order->getState());
                        },
                    ],
                ],
            ]);

        if (null === $customer) {
            $builder->addColumn('customer', Type\Column\SaleCustomerType::class, [
                'position' => 30,
            ]);
        }

        if (!empty($filters)) {
            return;
        }

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
            ->addFilter('email', CType\Filter\TextType::class, [
                'label'    => t('field.email', [], 'EkynaUi'),
                'position' => 30,
            ])
            ->addFilter('company', CType\Filter\TextType::class, [
                'label'    => t('field.company', [], 'EkynaUi'),
                'position' => 31,
            ])
            ->addFilter('firstName', CType\Filter\TextType::class, [
                'label'    => t('field.first_name', [], 'EkynaUi'),
                'position' => 32,
            ])
            ->addFilter('lastName', CType\Filter\TextType::class, [
                'label'    => t('field.last_name', [], 'EkynaUi'),
                'position' => 33,
            ])
            ->addFilter('companyNumber', CType\Filter\TextType::class, [
                'label'         => t('customer.field.company_number', [], 'EkynaCommerce'),
                'property_path' => 'customer.companyNumber',
                'position'      => 34,
            ])
            ->addFilter('customerGroup', ResourceType::class, [
                'resource' => 'ekyna_commerce.customer_group',
                'position' => 35,
            ])
            ->addFilter('title', CType\Filter\TextType::class, [
                'label'    => t('field.title', [], 'EkynaUi'),
                'position' => 40,
            ])
            ->addFilter('voucherNumber', CType\Filter\TextType::class, [
                'label'    => t('sale.field.voucher_number', [], 'EkynaCommerce'),
                'position' => 45,
            ])
            ->addFilter('originNumber', CType\Filter\TextType::class, [
                'label'    => t('sale.field.origin_number', [], 'EkynaCommerce'),
                'position' => 50,
            ])
            ->addFilter('grandTotal', CType\Filter\NumberType::class, [
                'label'    => t('sale.field.ati_total', [], 'EkynaCommerce'),
                'position' => 60,
            ])
            ->addFilter('paidTotal', CType\Filter\NumberType::class, [
                'label'    => t('sale.field.paid_total', [], 'EkynaCommerce'),
                'position' => 70,
            ])
            ->addFilter('state', CType\Filter\ChoiceType::class, [
                'label'    => t('field.status', [], 'EkynaCommerce'),
                'choices'  => Model\OrderStates::getChoices(),
                'position' => 80,
            ])
            ->addFilter('paymentMethod', ResourceType::class, [
                'resource' => 'ekyna_commerce.payment_method',
                'position' => 90,
            ])
            ->addFilter('paymentState', CType\Filter\ChoiceType::class, [
                'label'    => t('sale.field.payment_state', [], 'EkynaCommerce'),
                'choices'  => Model\PaymentStates::getChoices([
                    PaymentStates::STATE_EXPIRED,
                    PaymentStates::STATE_SUSPENDED,
                    PaymentStates::STATE_UNKNOWN,
                ]),
                'position' => 100,
            ])
            ->addFilter('shipmentState', CType\Filter\ChoiceType::class, [
                'label'    => t('sale.field.shipment_state', [], 'EkynaCommerce'),
                'choices'  => Model\ShipmentStates::getChoices([
                    //ShipmentStates::STATE_NEW,
                    ShipmentStates::STATE_SHIPPED,
                    ShipmentStates::STATE_NONE,
                ]),
                'position' => 110,
            ])
            ->addFilter('invoiceState', CType\Filter\ChoiceType::class, [
                'label'    => t('sale.field.invoice_state', [], 'EkynaCommerce'),
                'choices'  => Model\InvoiceStates::getChoices([
                    //InvoiceStates::STATE_NEW,
                    InvoiceStates::STATE_INVOICED,
                ]),
                'position' => 120,
            ])
            ->addFilter('inCharge', Type\Filter\InChargeType::class, [
                'position' => 120,
            ])
            ->addFilter('sample', CType\Filter\BooleanType::class, [
                'label'    => t('field.sample', [], 'EkynaCommerce'),
                'position' => 130,
            ])
            ->addFilter('tags', Type\Filter\SaleTagsType::class, [
                'position' => 140,
            ])
            ->addFilter('inCharge', Type\Filter\InChargeType::class, [
                'position' => 145,
            ])
            ->addFilter('subject', Type\Filter\SaleSubjectType::class, [
                'position' => 150,
            ]);

        $builder
            ->addAction('prepare', Type\Action\OrderPrepareActionType::class)
            ->addAction('abort', Type\Action\OrderAbortActionType::class);
    }

    public function buildRowView(View\RowView $view, RowInterface $row, array $options): void
    {
        /** @var OrderInterface $order */
        $order = $row->getData(null);

        if ($order->isFirst()) {
            $view->vars['attr']['class'] = 'success';
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
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
