<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\CommerceBundle\Model\PaymentStates;
use Ekyna\Bundle\CommerceBundle\Table\Column;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type\Filter\EntityType;
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
     * @var string
     */
    private $paymentMethodClass;


    /**
     * @inheritDoc
     */
    public function __construct($class, $paymentMethodClass)
    {
        parent::__construct($class);

        $this->paymentMethodClass = $paymentMethodClass;
    }

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
            ->addColumn('method', CType\Column\TextType::class, [
                'label'         => 'ekyna_core.field.method',
                'property_path' => 'method.name',
                'position'      => 30,
            ])
            ->addColumn('amount', BType\Column\PriceType::class, [
                'label'         => 'ekyna_core.field.amount',
                'currency_path' => 'currency.code',
                'position'      => 40,
            ])
            ->addColumn('currency', CType\Column\TextType::class, [
                'label'         => 'ekyna_core.field.currency',
                'property_path' => 'currency.name',
                'position'      => 50,
            ])
            ->addColumn('state', Column\PaymentStateType::class, [
                'label'    => 'ekyna_core.field.status',
                'position' => 60,
            ])
            ->addColumn('outstandingDate', CType\Column\DateTimeType::class, [
                'label'         => 'ekyna_commerce.sale.field.outstanding_date',
                'property_path' => 'order.outstandingDate',
                'time_format'   => 'none',
                'position'      => 70,
            ])
            ->addColumn('createdAt', CType\Column\DateTimeType::class, [
                'label'       => 'ekyna_core.field.created_at',
                'time_format' => 'none',
                'position'    => 80,
            ])
            ->addColumn('completedAt', CType\Column\DateTimeType::class, [
                'label'       => 'ekyna_core.field.completed_at',
                'time_format' => 'none',
                'position'    => 90,
            ]);

        if ($options['order'] || $options['customer']) {
            return;
        }

        $builder
            ->addFilter('number', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.number',
                'position' => 10,
            ])
            ->addFilter('method', EntityType::class, [
                'label'    => 'ekyna_core.field.method',
                'class'    => $this->paymentMethodClass,
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
