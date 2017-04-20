<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\CommerceBundle\Model\ShipmentStates;
use Ekyna\Bundle\CommerceBundle\Table\Action;
use Ekyna\Bundle\CommerceBundle\Table\Column;
use Ekyna\Bundle\ResourceBundle\Table\Filter\ResourceType;
use Ekyna\Component\Commerce\Document\Model\DocumentTypes;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentLabelInterface;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;

use function Symfony\Component\Translation\t;

/**
 * Class OrderShipmentType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderShipmentType extends AbstractOrderListType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        parent::buildTable($builder, $options);

        $builder
            ->addDefaultSort('createdAt', ColumnSort::DESC)
            ->addColumn('number', Column\OrderShipmentType::class, [
                'label'         => t('field.number', [], 'EkynaUi'),
                'property_path' => false,
                'position'      => 10,
            ])
            ->addColumn('return', CType\Column\BooleanType::class, [
                'label'       => t('shipment.field.return', [], 'EkynaCommerce'),
                'true_class'  => 'label-warning',
                'false_class' => 'label-default',
                'position'    => 20,
            ])
            /*->addColumn('customer', Column\SaleCustomerType::class, [
                'label'         => t('customer.label.singular', [], 'EkynaCommerce'),
                'property_path' => 'order',
                'position'      => 30,
            ])*/
            ->addColumn('method', CType\Column\TextType::class, [
                'label'         => t('field.method', [], 'EkynaUi'),
                'property_path' => 'method.name',
                'position'      => 40,
            ])
            ->addColumn('state', Column\ShipmentStateType::class, [
                'label'    => t('field.status', [], 'EkynaUi'),
                'position' => 50,
            ])
            ->addColumn('weight', Column\ShipmentWeightType::class, [
                'position' => 60,
            ])
            ->addColumn('trackingNumber', Column\ShipmentTrackingNumberType::class, [
                'position' => 70,
            ])
            ->addColumn('createdAt', CType\Column\DateTimeType::class, [
                'label'       => t('field.created_at', [], 'EkynaUi'),
                'time_format' => 'none',
                'position'    => 80,
            ])
            ->addColumn('completedAt', CType\Column\DateTimeType::class, [
                'label'       => t('field.completed_at', [], 'EkynaUi'),
                'time_format' => 'none',
                'position'    => 90,
            ])
            ->addColumn('actions', Column\ShipmentActionsType::class, [
                'position' => 999,
            ]);

        if ($options['order'] || $options['customer']) {
            return;
        }

        $builder
            ->addFilter('number', CType\Filter\TextType::class, [
                'label'    => t('field.number', [], 'EkynaUi'),
                'position' => 10,
            ])
            ->addFilter('return', CType\Filter\BooleanType::class, [
                'label'    => t('shipment.field.return', [], 'EkynaCommerce'),
                'position' => 20,
            ])
            ->addFilter('method', ResourceType::class, [
                'resource' => 'ekyna_commerce.shipment_method',
                'position' => 30,
            ])
            ->addFilter('state', CType\Filter\ChoiceType::class, [
                'label'    => t('field.status', [], 'EkynaUi'),
                'choices'  => ShipmentStates::getChoices(),
                'position' => 40,
            ])
            ->addFilter('weight', CType\Filter\NumberType::class, [
                'label'    => t('field.weight', [], 'EkynaUi'),
                'position' => 50,
            ])
            ->addFilter('trackingNumber', CType\Filter\TextType::class, [
                'label'    => t('shipment.field.tracking_number', [], 'EkynaCommerce'),
                'position' => 60,
            ])
            ->addFilter('createdAt', CType\Filter\DateTimeType::class, [
                'label'    => t('field.created_at', [], 'EkynaUi'),
                'position' => 70,
            ])
            ->addFilter('shippedAt', CType\Filter\DateTimeType::class, [
                'label'    => t('shipment.field.shipped_at', [], 'EkynaCommerce'),
                'position' => 80,
            ]);

        $builder
            ->addAction('ship', Action\ShipmentShipActionType::class)
            ->addAction('shipment_label', Action\ShipmentPrintLabelActionType::class)
            ->addAction('summary_label', Action\ShipmentPrintLabelActionType::class, [
                'label' => t('shipment.action.summary_labels', [], 'EkynaCommerce'),
                'types' => [ShipmentLabelInterface::TYPE_SUMMARY],
            ])
            ->addAction('prepare', Action\ShipmentPrepareActionType::class)
            ->addAction('cancel', Action\ShipmentCancelActionType::class)
            ->addAction('remove', Action\ShipmentRemoveActionType::class)
            ->addAction('bills', Action\ShipmentDocumentActionType::class, [
                'label' => t('shipment.action.bills', [], 'EkynaCommerce'),
                'type'  => DocumentTypes::TYPE_SHIPMENT_BILL,
            ])
            ->addAction('forms', Action\ShipmentDocumentActionType::class, [
                'label' => t('shipment.action.forms', [], 'EkynaCommerce'),
                'type'  => DocumentTypes::TYPE_SHIPMENT_FORM,
            ]);
    }
}
