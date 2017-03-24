<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class PaymentTermType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentTermType extends ResourceTableType
{
    /**
     * {@inheritdoc}
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
            ->addColumn('id', 'id')
            ->addColumn('name', 'anchor', [
                'label'                => 'ekyna_core.field.name',
                'route_name'           => 'ekyna_commerce_payment_term_admin_show',
                'route_parameters_map' => [
                    'paymentTermId' => 'id',
                ],
                'position'             => 10,
            ])
            ->addColumn('days', 'number', [
                'label'     => 'ekyna_commerce.payment_term.field.days',
                'precision' => 0,
                'position'  => 20,
            ])
            ->addColumn('endOfMonth', 'boolean', [
                'label'    => 'ekyna_commerce.payment_term.field.end_of_month',
                'position' => 30,
            ])
            ->addColumn('actions', 'admin_actions', [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_commerce_payment_term_admin_edit',
                        'route_parameters_map' => ['paymentTermId' => 'id'],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_commerce_payment_term_admin_remove',
                        'route_parameters_map' => ['paymentTermId' => 'id'],
                        'permission'           => 'delete',
                    ],
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_commerce_payment_term';
    }
}
