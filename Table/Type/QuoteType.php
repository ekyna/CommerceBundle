<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\CommerceBundle\Model;
use Ekyna\Component\Table\TableBuilderInterface;

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
        $builder
            ->addColumn('id', 'id', [
                'sortable' => true,
            ])
            ->addColumn('number', 'anchor', [
                'label'                => 'ekyna_core.field.number',
                'sortable'             => true,
                'route_name'           => 'ekyna_commerce_quote_admin_show',
                'route_parameters_map' => [
                    'quoteId' => 'id',
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
            ->addColumn('grandTotal', 'price', [
                'label'         => 'ekyna_commerce.sale.field.grand_total',
                'sortable'      => true,
                'currency_path' => 'currency.code',
                'position'      => 40,
            ])
            ->addColumn('paidTotal', 'price', [
                'label'         => 'ekyna_commerce.sale.field.paid_total',
                'sortable'      => true,
                'currency_path' => 'currency.code',
                'position'      => 50,
            ])
            ->addColumn('state', 'ekyna_commerce_sale_state', [
                'label'    => 'ekyna_commerce.sale.field.state',
                'position' => 60,
            ])
            ->addColumn('paymentState', 'ekyna_commerce_payment_state', [
                'label'    => 'ekyna_commerce.sale.field.payment_state',
                'position' => 70,
            ])
            ->addColumn('actions', 'admin_actions', [
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
            ->addFilter('granTotal', 'number', [
                'label'    => 'ekyna_commerce.sale.field.grand_total',
                'position' => 40,
            ])
            ->addFilter('paidTotal', 'number', [
                'label'    => 'ekyna_commerce.sale.field.paid_total',
                'position' => 50,
            ])
            ->addFilter('state', 'choice', [
                'label'    => 'ekyna_commerce.sale.field.state',
                'choices'  => Model\OrderStates::getChoices(),
                'position' => 60,
            ])
            ->addFilter('paymentState', 'choice', [
                'label'    => 'ekyna_commerce.sale.field.payment_state',
                'choices'  => Model\PaymentStates::getChoices(),
                'position' => 70,
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
        return 'ekyna_commerce_quote';
    }
}
