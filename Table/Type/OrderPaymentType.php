<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\CommerceBundle\Model\PaymentStates;
use Ekyna\Bundle\CommerceBundle\Table\Column\PaymentStateType;
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

        $filters = null === $options['order'];

        $builder
            ->addDefaultSort('createdAt', ColumnSort::DESC)
            ->addColumn('number', CType\Column\TextType::class, [
                'label'    => 'ekyna_core.field.number',
                'position' => 10,
            ])
            ->addColumn('method', CType\Column\TextType::class, [
                'label'         => 'ekyna_core.field.method',
                'property_path' => 'method.name',
                'position'      => 20,
            ])
            ->addColumn('amount', BType\Column\PriceType::class, [
                'label'         => 'ekyna_core.field.amount',
                'currency_path' => 'currency.code',
                'position'      => 30,
            ])
            ->addColumn('currency', CType\Column\TextType::class, [
                'label'         => 'ekyna_core.field.currency',
                'property_path' => 'currency.name',
                'position'      => 40,
            ])
            ->addColumn('state', PaymentStateType::class, [
                'label'    => 'ekyna_core.field.status',
                'position' => 50,
            ])
            ->addColumn('createdAt', CType\Column\DateTimeType::class, [
                'label'       => 'ekyna_core.field.created_at',
                'time_format' => 'none',
                'position'    => 70,
            ]);

        if ($filters) {
            $builder
                ->addFilter('number', CType\Filter\TextType::class, [
                    'label'    => 'ekyna_core.field.number',
                    'position' => 10,
                ])
                ->addFilter('method', EntityType::class, [
                    'label'    => 'ekyna_core.field.method',
                    'class'    => $this->paymentMethodClass,
                    'position' => 20,
                ])
                ->addFilter('amount', CType\Filter\NumberType::class, [
                    'label'    => 'ekyna_core.field.amount',
                    'position' => 30,
                ])
                ->addFilter('state', CType\Filter\ChoiceType::class, [
                    'label'    => 'ekyna_core.field.status',
                    'choices'  => PaymentStates::getChoices(),
                    'position' => 40,
                ])
                ->addFilter('createdAt', CType\Filter\DateTimeType::class, [
                    'label'    => 'ekyna_core.field.created_at',
                    'position' => 50,
                ]);
        }
    }
}
