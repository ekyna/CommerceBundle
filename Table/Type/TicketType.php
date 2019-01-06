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
            ->addColumn('state', Column\TicketStateType::class, [
                'label'    => 'ekyna_commerce.field.status',
                'position' => 20,
            ])
            ->addColumn('subject', CType\Column\TextType::class, [
                'label'    => 'ekyna_core.field.subject',
                'position' => 30,
            ])
            ->addColumn('customer', Column\CustomerType::class, [
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
            ->addColumn('createdAt', CType\Column\DateTimeType::class, [
                'label'       => 'ekyna_core.field.date',
                'position'    => 70,
                'time_format' => 'none',
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_commerce_supplier_order_admin_remove',
                        'route_parameters_map' => [
                            'supplierOrderId' => 'id',
                        ],
                        'permission'           => 'delete',
                    ],
                ],
            ])
            ->addFilter('number', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.number',
                'position' => 10,
            ])
            ->addFilter('state', CType\Filter\ChoiceType::class, [
                'label'    => 'ekyna_commerce.field.status',
                'choices'  => TicketStates::getChoices(),
                'position' => 20,
            ])
            ->addFilter('subject', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.subject',
                'position' => 30,
            ])
            ->addFilter('customer', Filter\CustomerType::class, [
                'position' => 40
            ])
            ->addFilter('orders', Filter\OrderType::class, [
                'position' => 50
            ])
            ->addFilter('quotes', Filter\QuoteType::class, [
                'position' => 60
            ])
            ->addFilter('createdAt', CType\Filter\DateTimeType::class, [
                'label'    => 'ekyna_core.field.created_at',
                'position' => 70,
                'time'     => false,
            ]);
    }
}
