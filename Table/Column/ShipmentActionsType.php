<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Bundle\CommerceBundle\Model\ShipmentGatewayActions;
use Ekyna\Bundle\CommerceBundle\Service\Shipment\ShipmentHelper;
use Ekyna\Bundle\TableBundle\Extension\Type\Column\ActionsType;
use Ekyna\Component\Commerce\Document\Model\DocumentTypes;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;

/**
 * Class ShipmentStateType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentActionsType extends AbstractColumnType
{
    /**
     * @var ShipmentHelper
     */
    private $shipmentHelper;


    /**
     * Constructor.
     *
     * @param ShipmentHelper $shipmentHelper
     */
    public function __construct(ShipmentHelper $shipmentHelper)
    {
        $this->shipmentHelper = $shipmentHelper;
    }

    /**
     * @inheritDoc
     */
    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options)
    {
        $shipment = $row->getData();
        if (!$shipment instanceof ShipmentInterface) {
            return;
        }

        $actions = $this->shipmentHelper->getGatewayShipmentActions($shipment);
        if (empty($actions)) {
            return;
        }

        $buttons = isset($view->vars['buttons']) ? $view->vars['buttons'] : [];

        // TODO Refactor
        /** @see \Ekyna\Bundle\CommerceBundle\Twig\ShipmentExtension */

        foreach ($actions as $action) {
            $buttons[] = [
                'label'      => ShipmentGatewayActions::getLabel($action),
                'icon'       => ShipmentGatewayActions::getIcon($action),
                'fa_icon'    => false,
                'class'      => ShipmentGatewayActions::getTheme($action),
                'confirm'    => ShipmentGatewayActions::getConfirm($action),
                'target'     => ShipmentGatewayActions::getTarget($action),
                'route'      => 'ekyna_commerce_order_shipment_admin_' . $action,
                'parameters' => [
                    'orderId'         => $shipment->getSale()->getId(),
                    'orderShipmentId' => $shipment->getId(),
                ],
                'disabled'   => false,
                //'permission' => 'EDIT', // TODO see admin actions type extension
            ];
        }

        // Bill document
        $buttons[] = [
            'label'      => 'ekyna_commerce.document.type.' . ($shipment->isReturn() ? 'return' : 'shipment') . '_bill',
            'icon'       => 'file',
            'fa_icon'    => 'true',
            'class'      => 'primary',
            'confirm'    => null,
            'target'     => '_blank',
            'route'      => 'ekyna_commerce_order_shipment_admin_render',
            'parameters' => [
                'orderId'         => $shipment->getSale()->getId(),
                'orderShipmentId' => $shipment->getId(),
                'type'            => DocumentTypes::TYPE_SHIPMENT_BILL,
            ],
            'disabled'   => false,
            //'permission' => 'EDIT', // TODO see admin actions type extension
        ];

        if (!ShipmentStates::isStockableState($shipment->getState())) {
            if (!$shipment->isReturn() && !$shipment->getSale()->isReleased()) {
                // Form document
                $buttons[] = [
                    'label'      => 'ekyna_commerce.document.type.shipment_form',
                    'icon'       => 'check-square-o',
                    'fa_icon'    => 'true',
                    'class'      => 'primary',
                    'confirm'    => null,
                    'target'     => '_blank',
                    'route'      => 'ekyna_commerce_order_shipment_admin_render',
                    'parameters' => [
                        'orderId'         => $shipment->getSale()->getId(),
                        'orderShipmentId' => $shipment->getId(),
                        'type'            => DocumentTypes::TYPE_SHIPMENT_FORM,
                    ],
                    'disabled'   => false,
                    //'permission' => 'EDIT', // TODO see admin actions type extension
                ];
            }

            if ($shipment->getState() === ShipmentStates::STATE_PREPARATION) {
                $buttons[] = [
                    'label'      => 'ekyna_core.button.edit',
                    'icon'       => 'pencil',
                    'fa_icon'    => 'true',
                    'class'      => 'warning',
                    'confirm'    => null,
                    'target'     => null,
                    'route'      => 'ekyna_commerce_order_shipment_admin_edit',
                    'parameters' => [
                        'orderId'         => $shipment->getSale()->getId(),
                        'orderShipmentId' => $shipment->getId(),
                    ],
                    'disabled'   => false,
                    //'permission' => 'EDIT', // TODO see admin actions type extension
                ];
            }

            // Remove
            $buttons[] = [
                'label'      => 'ekyna_core.button.remove',
                'icon'       => 'trash',
                'fa_icon'    => 'true',
                'class'      => 'danger',
                'confirm'    => null,
                'target'     => null,
                'route'      => 'ekyna_commerce_order_shipment_admin_remove',
                'parameters' => [
                    'orderId'         => $shipment->getSale()->getId(),
                    'orderShipmentId' => $shipment->getId(),
                ],
                'disabled'   => false,
                //'permission' => 'EDIT', // TODO see admin actions type extension
            ];
        }

        $view->vars['buttons'] = $buttons;
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'actions';
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return ActionsType::class;
    }
}
