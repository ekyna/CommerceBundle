<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\CommerceBundle\Model\ShipmentStates;
use Ekyna\Bundle\CommerceBundle\Service\Shipment\ShipmentHelper;
use Ekyna\Bundle\CommerceBundle\Table\Action;
use Ekyna\Bundle\CommerceBundle\Table\Column;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentLabelInterface;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type\Filter\EntityType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;
use Ekyna\Component\Table\View\RowView;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class OrderShipmentType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderShipmentType extends AbstractOrderListType
{
    /**
     * @var ShipmentHelper
     */
    private $shipmentHelper;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $shipmentMethodClass;


    /**
     * Constructor.
     *
     * @param ShipmentHelper      $shipmentHelper
     * @param TranslatorInterface $translator
     * @param string              $class
     * @param string              $shipmentMethodClass
     */
    public function __construct(
        ShipmentHelper $shipmentHelper,
        TranslatorInterface $translator,
        $class,
        $shipmentMethodClass
    ) {
        parent::__construct($class);

        $this->shipmentHelper = $shipmentHelper;
        $this->translator = $translator;
        $this->shipmentMethodClass = $shipmentMethodClass;
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
            ->addColumn('return', CType\Column\BooleanType::class, [
                'label'       => 'ekyna_commerce.shipment.field.return',
                'true_class'  => 'label-warning',
                'false_class' => 'label-default',
                'position'    => 20,
            ])
            /*->addColumn('customer', Column\SaleCustomerType::class, [
                'label'         => 'ekyna_commerce.customer.label.singular',
                'property_path' => 'order',
                'position'      => 30,
            ])*/
            ->addColumn('method', CType\Column\TextType::class, [
                'label'         => 'ekyna_core.field.method',
                'property_path' => 'method.name',
                'position'      => 40,
            ])
            ->addColumn('state', Column\ShipmentStateType::class, [
                'label'    => 'ekyna_core.field.status',
                'position' => 50,
            ])
            ->addColumn('weight', Column\ShipmentWeightType::class, [
                'position' => 60,
            ])
            ->addColumn('trackingNumber', Column\ShipmentTrackingNumberType::class, [
                'position' => 70,
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
            ])
            ->addColumn('actions', Column\ShipmentActionsType::class, [
                'position' => 999,
            ]);

        if ($options['order'] || $options['customer']) {
            return;
        }

        $builder
            ->addFilter('number', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.number',
                'position' => 10,
            ])
            ->addFilter('return', CType\Filter\BooleanType::class, [
                'label'    => 'ekyna_commerce.shipment.field.return',
                'position' => 20,
            ])
            ->addFilter('method', EntityType::class, [
                'label'    => 'ekyna_core.field.method',
                'class'    => $this->shipmentMethodClass,
                'position' => 30,
            ])
            ->addFilter('state', CType\Filter\ChoiceType::class, [
                'label'    => 'ekyna_core.field.status',
                'choices'  => ShipmentStates::getChoices(),
                'position' => 40,
            ])
            ->addFilter('weight', CType\Filter\NumberType::class, [
                'label'    => 'ekyna_core.field.weight',
                'position' => 50,
            ])
            ->addFilter('trackingNumber', CType\Filter\TextType::class, [
                'label'    => 'ekyna_commerce.shipment.field.tracking_number',
                'position' => 60,
            ])
            ->addFilter('createdAt', CType\Filter\DateTimeType::class, [
                'label'    => 'ekyna_core.field.created_at',
                'position' => 70,
            ])
            ->addFilter('completedAt', CType\Filter\DateTimeType::class, [
                'label'    => 'ekyna_core.field.completed_at',
                'position' => 80,
            ]);

        $builder
            ->addAction('ship', Action\ShipmentShipActionType::class)
            ->addAction('shipment_label', Action\ShipmentPrintLabelActionType::class)
            ->addAction('summary_label', Action\ShipmentPrintLabelActionType::class, [
                'label' => 'ekyna_commerce.shipment.action.summary_labels',
                'types' => [ShipmentLabelInterface::TYPE_SUMMARY],
            ])
            ->addAction('prepare', Action\ShipmentPrepareActionType::class)
            ->addAction('cancel', Action\ShipmentCancelActionType::class)
            ->addAction('remove', Action\ShipmentRemoveActionType::class)
            ->addAction('bills', Action\ShipmentDocumentActionType::class, [
                'label' => 'ekyna_commerce.shipment.action.bills',
                'type'  => 'bill',
            ])->addAction('forms', Action\ShipmentDocumentActionType::class, [
                'label' => 'ekyna_commerce.shipment.action.forms',
                'type'  => 'form',
            ]);
    }

    /**
     * @inheritDoc
     */
    public function buildRowView(RowView $view, RowInterface $row, array $options)
    {
        /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface $shipment */
        $shipment = $row->getData();

        $view->vars['attr']['data-summary'] = json_encode([
            'route'      => 'ekyna_commerce_order_shipment_admin_summary',
            'parameters' => [
                'orderId'         => $shipment->getSale()->getId(),
                'orderShipmentId' => $shipment->getId(),
            ],
        ]);
    }
}
