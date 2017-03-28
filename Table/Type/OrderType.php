<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\CommerceBundle\Model;
use Ekyna\Component\Table\TableBuilderInterface;

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
        $builder
            ->addColumn('id', 'id', [
                'sortable' => true,
            ])
            ->addColumn('number', 'anchor', [
                'label'                => 'ekyna_core.field.number',
                'sortable'             => true,
                'route_name'           => 'ekyna_commerce_order_admin_show',
                'route_parameters_map' => [
                    'orderId' => 'id',
                ],
                'position'             => 10,
            ])
            ->addColumn('customer', 'ekyna_commerce_sale_customer', [
                'label'    => 'ekyna_commerce.customer.label.singular',
                //'sortable' => true,
                'position' => 20,
            ])
            ->addColumn('voucherNumber', 'text', [
                'label'    => 'ekyna_commerce.sale.field.voucher_number',
                'sortable' => true,
                'position' => 30,
            ])
            ->addColumn('originNumber', 'text', [
                'label'    => 'ekyna_commerce.sale.field.origin_number',
                'sortable' => true,
                'position' => 40,
            ])
            ->addColumn('grandTotal', 'price', [
                'label'         => 'ekyna_commerce.sale.field.grand_total',
                'sortable'      => true,
                'currency_path' => 'currency.code',
                'position'      => 50,
            ])
            ->addColumn('paidTotal', 'price', [
                'label'         => 'ekyna_commerce.sale.field.paid_total',
                'sortable'      => true,
                'currency_path' => 'currency.code',
                'position'      => 60,
            ])
            ->addColumn('state', 'ekyna_commerce_sale_state', [
                'label'    => 'ekyna_commerce.sale.field.state',
                'position' => 70,
            ])
            ->addColumn('paymentState', 'ekyna_commerce_payment_state', [
                'label'    => 'ekyna_commerce.sale.field.payment_state',
                'position' => 80,
            ])
            ->addColumn('shipmentState', 'ekyna_commerce_shipment_state', [
                'label'    => 'ekyna_commerce.sale.field.shipment_state',
                'position' => 90,
            ])
            ->addColumn('actions', 'admin_actions', [
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
            ])
            ->addFilter('number', 'text', [
                'label'    => 'ekyna_core.field.number',
                'position' => 10,
            ])
            ->addFilter('email', 'text', [
                'label'    => 'ekyna_core.field.email',
                'position' => 20,
            ])
            ->addFilter('company', 'text', [
                'label'    => 'ekyna_core.field.company',
                'position' => 21,
            ])
            ->addFilter('firstName', 'text', [
                'label'    => 'ekyna_core.field.first_name',
                'position' => 22,
            ])
            ->addFilter('lastName', 'text', [
                'label'    => 'ekyna_core.field.last_name',
                'position' => 23,
            ])
            ->addFilter('voucherNumber', 'text', [
                'label'    => 'ekyna_commerce.sale.field.voucher_number',
                'position' => 30,
            ])
            ->addFilter('originNumber', 'text', [
                'label'    => 'ekyna_commerce.sale.field.origin_number',
                'position' => 40,
            ])
            ->addFilter('granTotal', 'number', [
                'label'    => 'ekyna_commerce.sale.field.grand_total',
                'position' => 50,
            ])
            ->addFilter('paidTotal', 'number', [
                'label'    => 'ekyna_commerce.sale.field.paid_total',
                'position' => 60,
            ])
            ->addFilter('state', 'choice', [
                'label'    => 'ekyna_commerce.sale.field.state',
                'choices'  => Model\OrderStates::getChoices(),
                'position' => 70,
            ])
            ->addFilter('paymentState', 'choice', [
                'label'    => 'ekyna_commerce.sale.field.payment_state',
                'choices'  => Model\PaymentStates::getChoices(),
                'position' => 80,
            ])
            ->addFilter('shipmentState', 'choice', [
                'label'    => 'ekyna_commerce.sale.field.shipment_state',
                'choices'  => Model\ShipmentStates::getChoices(),
                'position' => 90,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    /*public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        if (null !== $group = $this->getUserGroup()) {
            $resolver->setDefaults([
                'customize_qb' => function (QueryBuilder $qb, $alias) use ($group) {
                    $qb
                        ->join($alias . '.group', 'g')
                        ->andWhere($qb->expr()->gte('g.position', $group->getPosition()));
                },
            ]);
        }
    }*/

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_commerce_order';
    }
}
