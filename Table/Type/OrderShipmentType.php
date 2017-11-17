<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\CommerceBundle\Model\PaymentStates;
use Ekyna\Bundle\CommerceBundle\Service\Shipment\ShipmentHelper;
use Ekyna\Bundle\CommerceBundle\Table\Action\ShipmentDocumentActionType;
use Ekyna\Bundle\CommerceBundle\Table\Action\ShipmentPlatformActionType;
use Ekyna\Bundle\CommerceBundle\Table\Column\ShipmentActionsType;
use Ekyna\Bundle\CommerceBundle\Table\Column\ShipmentStateType;
use Ekyna\Component\Commerce\Shipment\Gateway\Action\ActionInterface;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type\Filter\EntityType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;
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

        $filters = null === $options['order'];

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
            ->addColumn('method', CType\Column\TextType::class, [
                'label'         => 'ekyna_core.field.method',
                'property_path' => 'method.name',
                'position'      => 30,
            ])
            ->addColumn('state', ShipmentStateType::class, [
                'label'    => 'ekyna_core.field.status',
                'position' => 40,
            ])
            ->addColumn('weight', CType\Column\NumberType::class, [
                'label'     => 'ekyna_core.field.weight',
                'precision' => 3,
                'append'    => 'kg',
                'position'  => 50,
            ])
            ->addColumn('trackingNumber', CType\Column\TextType::class, [
                'label'    => 'ekyna_commerce.shipment.field.tracking_number',
                'position' => 60,
            ])
            ->addColumn('createdAt', CType\Column\DateTimeType::class, [
                'label'       => 'ekyna_core.field.created_at',
                'time_format' => 'none',
                'position'    => 70,
            ])
            ->addColumn('actions', ShipmentActionsType::class, [
                'position' => 999,
            ]);

        if ($filters) {
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
                    'choices'  => PaymentStates::getChoices(),
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
                ]);
        }

        $platforms = $this->shipmentHelper->getPlatformsActionsNames(ActionInterface::SCOPE_PLATFORM);

        foreach ($platforms as $name => $platformActions) {
            foreach ($platformActions as $action) {
                $label = $this->translator->trans($this->shipmentHelper->getActionLabel($action));

                $builder->addAction("{$name}_{$action}", ShipmentPlatformActionType::class, [
                    'label'    => sprintf('[%s] %s', ucfirst($name), $label),
                    'platform' => $name,
                    'action'   => $action,
                ]);
            }
        }

        $builder->addAction('bills', ShipmentDocumentActionType::class, [
            'label' => 'ekyna_commerce.shipment.action.bills',
            'type'  => 'bill',
        ]);

        $builder->addAction('forms', ShipmentDocumentActionType::class, [
            'label' => 'ekyna_commerce.shipment.action.forms',
            'type'  => 'form',
        ]);
    }
}
