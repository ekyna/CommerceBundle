<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Action;

use Ekyna\Bundle\CommerceBundle\Service\Shipment\LabelRenderer;
use Ekyna\Component\Commerce\Exception\ShipmentGatewayException;
use Ekyna\Component\Commerce\Shipment\Entity\AbstractShipmentLabel;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayActions;
use Ekyna\Component\Commerce\Shipment\Gateway\PersisterInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\RegistryInterface;
use Ekyna\Component\Table\Action\AbstractActionType;
use Ekyna\Component\Table\Action\ActionInterface;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\TableError;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ShipmentPrintLabelActionType
 * @package Ekyna\Bundle\CommerceBundle\Table\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentPrintLabelActionType extends AbstractActionType
{
    /**
     * @var RegistryInterface
     */
    private $gatewayRegistry;

    /**
     * @var PersisterInterface
     */
    private $shipmentPersister;

    /**
     * @var LabelRenderer
     */
    private $labelRenderer;


    /**
     * Constructor.
     *
     * @param RegistryInterface  $gatewayRegistry
     * @param PersisterInterface $shipmentPersister
     * @param LabelRenderer      $labelRenderer
     */
    public function __construct(
        RegistryInterface $gatewayRegistry,
        PersisterInterface $shipmentPersister,
        LabelRenderer $labelRenderer
    ) {
        $this->gatewayRegistry = $gatewayRegistry;
        $this->shipmentPersister = $shipmentPersister;
        $this->labelRenderer = $labelRenderer;
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

        $shipments = array_map(function (RowInterface $row) {
            return $row->getData();
        }, $rows);

        $types = $options['types'];
        $labels = [];

        try {
            /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface $shipment */
            foreach ($shipments as $shipment) {
                $gateway = $this->gatewayRegistry->getGateway($shipment->getGatewayName());

                if (!$gateway->can($shipment, GatewayActions::PRINT_LABEL)) {
                    continue;
                }

                foreach ($gateway->printLabel($shipment, $types) as $label) {
                    $labels[] = $label;
                }
            }

            $this->shipmentPersister->flush();
        } catch (ShipmentGatewayException $e) {
            $table->addError(new TableError($e->getMessage()));

            return true;
        }

        if (!empty($labels)) {
            return $this->labelRenderer->render($labels);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'label' => 'ekyna_commerce.shipment.action.shipment_labels',
                'types' => [AbstractShipmentLabel::TYPE_SHIPMENT],
            ])
            ->setAllowedTypes('types', 'array')
            ->setAllowedValues('types', function($value) {
                if (!is_array($value) || empty($value)) {
                    return false;
                }

                foreach ($value as $t) {
                    if (!in_array($t, AbstractShipmentLabel::getTypes(), true)) {
                        return false;
                    }
                }

                return true;
            });
    }
}
