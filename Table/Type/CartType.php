<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class CartType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartType extends ResourceTableType
{
    /**
     * {@inheritdoc}
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        /*$builder
            ->addColumn('id', 'number', [
                'sortable' => true,
            ])
            ->addColumn('number', 'anchor', [
                'label'                => 'ekyna_core.field.number',
                'sortable'             => true,
                'route_name'           => 'ekyna_commerce_order_admin_show',
                'route_parameters_map' => [
                    'orderId' => 'id',
                ],
            ])
            ->addColumn('customer', 'ekyna_commerce_order_customer', [
                'label'    => 'ekyna_commerce.customer.label.singular',
                'sortable' => true,
            ])
            ->addColumn('grandTotal', 'price', [
                'label'         => 'ekyna_commerce.order.field.grand_total',
                'sortable'      => true,
                'currency_path' => 'currency.code',
            ])
            ->addColumn('state', 'ekyna_commerce_order_state', [
                'label' => 'ekyna_commerce.order.field.state',
            ])
            ->addColumn('paymentState', 'ekyna_commerce_payment_state', [
                'label' => 'ekyna_commerce.order.field.payment_state',
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
                'label' => 'ekyna_core.field.number',
            ])
            ->addFilter('email', 'text', [
                'label' => 'ekyna_core.field.email',
            ])
            ->addFilter('company', 'text', [
                'label' => 'ekyna_core.field.company',
            ])
            ->addFilter('firstName', 'text', [
                'label' => 'ekyna_core.field.first_name',
            ])
            ->addFilter('lastName', 'text', [
                'label' => 'ekyna_core.field.last_name',
            ])/*->addFilter('atiTotal', 'number', [
                'label' => 'ekyna_order.order.field.ati_total',
            ])
            ->addFilter('state', 'choice', [
                'label' => 'ekyna_order.order.field.state',
                'choices' => OrderStates::getChoices(),
            ])
            ->addFilter('paymentState', 'choice', [
                'label' => 'ekyna_order.order.field.payment_state',
                'choices' => PaymentStates::getChoices(),
            ])*/
        ;
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
}
