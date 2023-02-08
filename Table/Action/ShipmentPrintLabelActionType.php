<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Action;

use Ekyna\Bundle\CommerceBundle\Service\Shipment\ShipmentLabelRenderer;
use Ekyna\Component\Commerce\Exception\ShipmentGatewayException;
use Ekyna\Component\Commerce\Shipment\Entity\AbstractShipmentLabel;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayActions;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayRegistryInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\PersisterInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentLabelInterface;
use Ekyna\Component\Resource\Exception\PdfException;
use Ekyna\Component\Table\Action\AbstractActionType;
use Ekyna\Component\Table\Action\ActionInterface;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\TableError;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_map;
use function is_array;
use function Symfony\Component\Translation\t;

/**
 * Class ShipmentPrintLabelActionType
 * @package Ekyna\Bundle\CommerceBundle\Table\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentPrintLabelActionType extends AbstractActionType
{
    public function __construct(
        private readonly GatewayRegistryInterface $gatewayRegistry,
        private readonly PersisterInterface $shipmentPersister,
        private readonly ShipmentLabelRenderer $labelRenderer
    ) {
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
            return $row->getData(null);
        }, $rows);

        $types = $options['types'];
        $labels = [];

        try {
            /** @var ShipmentInterface $shipment */
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
            try {
                return $this->labelRenderer->render($labels);
            } catch (PdfException $e) {
                $table->addError(new TableError($e->getMessage()));
            }
        }

        return true;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => t('shipment.action.shipment_labels', [], 'EkynaCommerce'),
                'types' => [ShipmentLabelInterface::TYPE_SHIPMENT],
            ])
            ->setAllowedTypes('types', 'array')
            ->setAllowedValues('types', function ($value) {
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
