<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Bundle\CommerceBundle\Service\Shipment\ShipmentHelper;
use Ekyna\Bundle\TableBundle\Extension\Type\Column\ActionsType;
use Ekyna\Component\Commerce\Shipment\Gateway\Action;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
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

        $names = $this->shipmentHelper->getGatewayActionsNames($shipment, Action\ActionInterface::SCOPE_GATEWAY);
        if (empty($names)) {
            return;
        }

        $buttons = isset($view->vars['buttons']) ? $view->vars['buttons'] : [];

        foreach ($names as $name) {
            $buttons[] = [
                'label'      => $this->shipmentHelper->getActionLabel($name),
                'icon'       => $this->shipmentHelper->getActionIcon($name),
                'class'      => 'primary',
                'route'      => 'ekyna_commerce_order_shipment_admin_gateway_action',
                'parameters' => [
                    'orderId'         => $shipment->getSale()->getId(),
                    'orderShipmentId' => $shipment->getId(),
                    'action'          => $name,
                ],
                'disabled'   => false, // TODO $gateway->supports(new $class($shipment)) ?
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
