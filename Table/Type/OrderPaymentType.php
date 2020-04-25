<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\CommerceBundle\Model\PaymentStates;
use Ekyna\Bundle\CommerceBundle\Table\Column;
use Ekyna\Bundle\ResourceBundle\Table\Filter\ResourceType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;

/**
 * Class OrderPaymentType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderPaymentType extends AbstractOrderListType
{
    /**
     * @inheritDoc
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        parent::buildTable($builder, $options);

        $builder
            ->addDefaultSort('createdAt', ColumnSort::DESC)
            ->addColumn('number', CType\Column\TextType::class, [
                'label'    => 'ekyna_core.field.number',
                'position' => 10,
            ])
            ->addColumn('refund', CType\Column\BooleanType::class, [
                'label'       => 'ekyna_core.field.type',
                'true_label'  => 'ekyna_commerce.refund.label.singular',
                'false_label' => 'ekyna_commerce.payment.label.singular',
                'true_class'  => 'label-warning',
                'false_class' => 'label-success',
                'position'    => 20,
            ])
            ->addColumn('method', CType\Column\TextType::class, [
                'label'         => 'ekyna_core.field.method',
                'property_path' => 'method.name',
                'position'      => 30,
            ])
            ->addColumn('amount', Column\CurrencyType::class, [
                'label'    => 'ekyna_core.field.amount',
                'position' => 40,
            ])
            ->addColumn('state', Column\PaymentStateType::class, [
                'label'    => 'ekyna_core.field.status',
                'position' => 50,
            ])
            ->addColumn('outstandingDate', Column\PaymentOutstandingDateType::class, [
                'position' => 60,
            ])
            ->addColumn('createdAt', CType\Column\DateTimeType::class, [
                'label'       => 'ekyna_core.field.created_at',
                'time_format' => 'none',
                'position'    => 70,
            ])
            ->addColumn('completedAt', CType\Column\DateTimeType::class, [
                'label'       => 'ekyna_core.field.completed_at',
                'time_format' => 'none',
                'position'    => 80,
            ]);

        if ($options['order'] || $options['customer']) {
            return;
        }

        $builder
            ->addFilter('number', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.number',
                'position' => 10,
            ])
            ->addFilter('refund', CType\Filter\BooleanType::class, [
                'label'    => 'ekyna_commerce.refund.label.singular',
                'position' => 20,
            ])
            ->addFilter('method', ResourceType::class, [
                'resource' => 'ekyna_commerce.payment_method',
                'position' => 40,
            ])
            ->addFilter('amount', CType\Filter\NumberType::class, [
                'label'    => 'ekyna_core.field.amount',
                'position' => 50,
            ])
            ->addFilter('state', CType\Filter\ChoiceType::class, [
                'label'    => 'ekyna_core.field.status',
                'choices'  => PaymentStates::getChoices(),
                'position' => 60,
            ])
            ->addFilter('outstandingDate', CType\Filter\DateTimeType::class, [
                'label'         => 'ekyna_commerce.sale.field.outstanding_date',
                'property_path' => 'order.outstandingDate',
                'position'      => 70,
            ])
            ->addFilter('createdAt', CType\Filter\DateTimeType::class, [
                'label'    => 'ekyna_core.field.created_at',
                'position' => 80,
            ])
            ->addFilter('completedAt', CType\Filter\DateTimeType::class, [
                'label'    => 'ekyna_core.field.completed_at',
                'position' => 80,
            ]);
    }
}
