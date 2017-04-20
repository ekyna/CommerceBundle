<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\CommerceBundle\Model\PaymentStates;
use Ekyna\Bundle\CommerceBundle\Table\Column;
use Ekyna\Bundle\ResourceBundle\Table\Filter\ResourceType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;

use function Symfony\Component\Translation\t;

/**
 * Class OrderPaymentType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderPaymentType extends AbstractOrderListType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        parent::buildTable($builder, $options);

        $builder
            ->addDefaultSort('createdAt', ColumnSort::DESC)
            ->addColumn('number', CType\Column\TextType::class, [
                'label'    => t('field.number', [], 'EkynaUi'),
                'position' => 10,
            ])
            ->addColumn('refund', CType\Column\BooleanType::class, [
                'label'       => t('field.type', [], 'EkynaUi'),
                'true_label'  => t('refund.label.singular', [], 'EkynaCommerce'),
                'false_label' => t('payment.label.singular', [], 'EkynaCommerce'),
                'true_class'  => 'label-warning',
                'false_class' => 'label-success',
                'position'    => 20,
            ])
            ->addColumn('method', CType\Column\TextType::class, [
                'label'         => t('field.method', [], 'EkynaUi'),
                'property_path' => 'method.name',
                'position'      => 30,
            ])
            ->addColumn('amount', Column\CurrencyType::class, [
                'label'    => t('field.amount', [], 'EkynaUi'),
                'position' => 40,
            ])
            ->addColumn('state', Column\PaymentStateType::class, [
                'label'    => t('field.status', [], 'EkynaUi'),
                'position' => 50,
            ])
            ->addColumn('outstandingDate', Column\PaymentOutstandingDateType::class, [
                'position' => 60,
            ])
            ->addColumn('createdAt', CType\Column\DateTimeType::class, [
                'label'       => t('field.created_at', [], 'EkynaUi'),
                'time_format' => 'none',
                'position'    => 70,
            ])
            ->addColumn('completedAt', CType\Column\DateTimeType::class, [
                'label'       => t('field.completed_at', [], 'EkynaUi'),
                'time_format' => 'none',
                'position'    => 80,
            ]);

        if ($options['order'] || $options['customer']) {
            return;
        }

        $builder
            ->addFilter('number', CType\Filter\TextType::class, [
                'label'    => t('field.number', [], 'EkynaUi'),
                'position' => 10,
            ])
            ->addFilter('refund', CType\Filter\BooleanType::class, [
                'label'    => t('refund.label.singular', [], 'EkynaCommerce'),
                'position' => 20,
            ])
            ->addFilter('method', ResourceType::class, [
                'resource' => 'ekyna_commerce.payment_method',
                'position' => 40,
            ])
            ->addFilter('amount', CType\Filter\NumberType::class, [
                'label'    => t('field.amount', [], 'EkynaUi'),
                'position' => 50,
            ])
            ->addFilter('state', CType\Filter\ChoiceType::class, [
                'label'    => t('field.status', [], 'EkynaUi'),
                'choices'  => PaymentStates::getChoices(),
                'position' => 60,
            ])
            ->addFilter('outstandingDate', CType\Filter\DateTimeType::class, [
                'label'         => t('sale.field.outstanding_date', [], 'EkynaCommerce'),
                'property_path' => 'order.outstandingDate',
                'position'      => 70,
            ])
            ->addFilter('createdAt', CType\Filter\DateTimeType::class, [
                'label'    => t('field.created_at', [], 'EkynaUi'),
                'position' => 80,
            ])
            ->addFilter('completedAt', CType\Filter\DateTimeType::class, [
                'label'    => t('field.completed_at', [], 'EkynaUi'),
                'position' => 80,
            ]);
    }
}
