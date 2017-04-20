<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Action;

use Ekyna\Component\Commerce\Exception\ShipmentGatewayException;
use Ekyna\Component\Commerce\Shipment\Gateway\PersisterInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayRegistryInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Table\Action\AbstractActionType;
use Ekyna\Component\Table\Action\ActionInterface;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\TableError;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_map;
use function Symfony\Component\Translation\t;

/**
 * Class ShipmentCancelActionType
 * @package Ekyna\Bundle\CommerceBundle\Table\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentCancelActionType extends AbstractActionType
{
    private GatewayRegistryInterface $gatewayRegistry;
    private PersisterInterface       $shipmentPersister;

    public function __construct(GatewayRegistryInterface $gatewayRegistry, PersisterInterface $shipmentPersister)
    {
        $this->gatewayRegistry = $gatewayRegistry;
        $this->shipmentPersister = $shipmentPersister;
    }

    /**
     * @inheritDoc
     */
    public function execute(ActionInterface $action, array $options)
    {
        $table = $action->getTable();

        // The selected row's
        $rows = $table->getSourceAdapter()->getSelection(
            $table->getContext()
        );

        $shipments = array_map(function(RowInterface $row) {
            return $row->getData(null);
        }, $rows);

        try {
            /** @var ShipmentInterface $shipment */
            foreach ($shipments as $shipment) {
                $gateway = $this->gatewayRegistry->getGateway($shipment->getGatewayName());

                if (ShipmentStates::isStockableState($shipment, false)) {
                    $gateway->cancel($shipment);
                } elseif ($shipment->getState() !== ShipmentStates::STATE_CANCELED) {
                    $shipment->setState(ShipmentStates::STATE_CANCELED);
                    $this->shipmentPersister->persist($shipment);
                }
            }

            $this->shipmentPersister->flush();
        } catch (ShipmentGatewayException $e) {
            $table->addError(new TableError($e->getMessage()));
        }

        return true;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('label', t('shipment.action.cancel', [], 'EkynaCommerce'));
    }
}
