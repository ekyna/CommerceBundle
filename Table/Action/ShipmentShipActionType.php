<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Action;

use Ekyna\Component\Commerce\Exception\ShipmentGatewayException;
use Ekyna\Component\Commerce\Shipment\Gateway\PersisterInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayRegistryInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Table\Action\AbstractActionType;
use Ekyna\Component\Table\Action\ActionInterface;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\TableError;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_map;
use function Symfony\Component\Translation\t;

/**
 * Class ShipmentShipActionType
 * @package Ekyna\Bundle\CommerceBundle\Table\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentShipActionType extends AbstractActionType
{
    private GatewayRegistryInterface $gatewayRegistry;
    private PersisterInterface       $shipmentPersister;


    /**
     * Constructor.
     *
     * @param GatewayRegistryInterface $gatewayRegistry
     * @param PersisterInterface       $shipmentPersister
     */
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

                $gateway->ship($shipment);
            }

            $this->shipmentPersister->flush();
        } catch (ShipmentGatewayException $e) {
            $table->addError(new TableError($e->getMessage()));
        }

        return true;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('label', t('shipment.action.ship', [], 'EkynaCommerce'));
    }
}
