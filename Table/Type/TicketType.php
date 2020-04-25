<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\CommerceBundle\Model\TicketStates;
use Ekyna\Bundle\CommerceBundle\Table\Column;
use Ekyna\Bundle\CommerceBundle\Table\Filter;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TicketType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketType extends ResourceTableType
{
    /**
     * @inheritDoc
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
            ->addDefaultSort('createdAt', ColumnSort::DESC)
            ->addColumn('number', BType\Column\AnchorType::class, [
                'label'                => 'ekyna_core.field.number',
                'route_name'           => 'ekyna_commerce_ticket_admin_show',
                'route_parameters_map' => [
                    'ticketId' => 'id',
                ],
                'position'             => 10,
            ])
            ->addColumn('internal', CType\Column\BooleanType::class, [
                'label'       => 'ekyna_commerce.field.internal',
                'true_class'  => 'label-danger',
                'false_class' => 'label-success',
                'position'    => 20,
            ])
            ->addColumn('state', Column\TicketStateType::class, [
                'label'      => 'ekyna_commerce.field.status',
                'admin_mode' => $options['admin_mode'],
                'position'   => 30,
            ])
            ->addColumn('subject', CType\Column\TextType::class, [
                'label'    => 'ekyna_core.field.subject',
                'position' => 40,
            ])
            ->addColumn('orders', Column\OrderType::class, [
                'multiple' => true,
                'position' => 50,
            ])
            ->addColumn('quotes', Column\QuoteType::class, [
                'multiple' => true,
                'position' => 60,
            ])
            ->addColumn('customer', Column\CustomerType::class, [
                'position' => 70,
            ])
            ->addColumn('inCharge', Column\InChargeType::class, [
                'position' => 80,
            ])
            ->addColumn('createdAt', CType\Column\DateTimeType::class, [
                'label'       => 'ekyna_core.field.date',
                'position'    => 90,
                'time_format' => 'none',
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_commerce_ticket_admin_remove',
                        'route_parameters_map' => [
                            'ticketId' => 'id',
                        ],
                        'permission'           => 'delete',
                    ],
                ],
            ])
            ->addFilter('number', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.number',
                'position' => 10,
            ])
            ->addFilter('internal', CType\Filter\BooleanType::class, [
                'label'    => 'ekyna_commerce.field.internal',
                'position' => 20,
            ])
            ->addFilter('state', CType\Filter\ChoiceType::class, [
                'label'    => 'ekyna_commerce.field.status',
                'choices'  => TicketStates::getChoices(),
                'position' => 30,
            ])
            ->addFilter('subject', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.subject',
                'position' => 40,
            ])
            ->addFilter('orders', Filter\OrderType::class, [
                'position' => 50,
            ])
            ->addFilter('quotes', Filter\QuoteType::class, [
                'position' => 60,
            ])
            ->addFilter('customer', Filter\CustomerType::class, [
                'position' => 70,
            ])
            ->addFilter('inCharge', Filter\InChargeType::class, [
                'position' => 50,
            ])
            ->addFilter('createdAt', CType\Filter\DateTimeType::class, [
                'label'    => 'ekyna_core.field.created_at',
                'position' => 90,
                'time'     => false,
            ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'configurable' => true,
            'profileable'  => true,
            'admin_mode'   => true,
        ]);
    }
}
