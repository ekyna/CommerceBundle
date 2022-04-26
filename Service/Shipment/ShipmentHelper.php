<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Shipment;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Exception\ShipmentGatewayException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Shipment\Gateway;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentAddressResolverInterface;

/**
 * Class ShipmentHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentHelper implements
    Shipment\WeightCalculatorAwareInterface,
    Shipment\AddressResolverAwareInterface,
    ShipmentAddressResolverInterface
{
    use Shipment\AddressResolverAwareTrait;
    use Shipment\WeightCalculatorAwareTrait;

    private Gateway\GatewayRegistryInterface $gatewayRegistry;


    public function __construct(Gateway\GatewayRegistryInterface $gatewayRegistry)
    {
        $this->gatewayRegistry = $gatewayRegistry;
    }

    /**
     * Returns the gateway registry.
     *
     * @return Gateway\GatewayRegistryInterface
     */
    public function getGatewayRegistry(): Gateway\GatewayRegistryInterface
    {
        return $this->gatewayRegistry;
    }

    /**
     * Returns the shipment's total weight.
     */
    public function getShipmentWeight(Shipment\ShipmentInterface $shipment): Decimal
    {
        if (0 < $shipment->getWeight()) {
            return $shipment->getWeight();
        }

        return $this->weightCalculator->calculateShipment($shipment);
    }

    public function getCountryRepository(): CountryRepositoryInterface
    {
        return $this->addressResolver->getCountryRepository();
    }

    public function resolveSenderAddress(
        Shipment\ShipmentInterface $shipment,
        bool $ignoreRelay = false
    ): AddressInterface {
        return $this->addressResolver->resolveSenderAddress($shipment, $ignoreRelay);
    }

    public function resolveReceiverAddress(
        Shipment\ShipmentInterface $shipment,
        bool $ignoreRelay = false
    ): AddressInterface {
        return $this->addressResolver->resolveReceiverAddress($shipment, $ignoreRelay);
    }

    /**
     * Returns the tracking url.
     */
    public function getTrackingUrl(Shipment\ShipmentDataInterface $shipmentData): ?string
    {
        if ($shipmentData instanceof Shipment\ShipmentInterface) {
            $shipment = $shipmentData;
        } elseif ($shipmentData instanceof Shipment\ShipmentParcelInterface) {
            $shipment = $shipmentData->getShipment();
        } else {
            throw new UnexpectedTypeException($shipmentData, [
                Shipment\ShipmentInterface::class,
                Shipment\ShipmentParcelInterface::class,
            ]);
        }

        $gateway = $this->gatewayRegistry->getGateway($shipment->getGatewayName());
        try {
            $url = $gateway->track($shipmentData);
        } catch (ShipmentGatewayException $e) {
            $url = null;
        }

        return $url;
    }

    /**
     * Returns the proof url.
     */
    public function getProofUrl(Shipment\ShipmentDataInterface $shipmentData): ?string
    {
        if ($shipmentData instanceof Shipment\ShipmentInterface) {
            $shipment = $shipmentData;
        } elseif ($shipmentData instanceof Shipment\ShipmentParcelInterface) {
            $shipment = $shipmentData->getShipment();
        } else {
            throw new UnexpectedTypeException($shipmentData, [
                Shipment\ShipmentInterface::class,
                Shipment\ShipmentParcelInterface::class,
            ]);
        }

        $gateway = $this->gatewayRegistry->getGateway($shipment->getGatewayName());
        try {
            $url = $gateway->prove($shipmentData);
        } catch (ShipmentGatewayException $e) {
            $url = null;
        }

        return $url;
    }

    /**
     * Returns whether the given shipment can be deleted.
     */
    public function isShipmentDeleteable(Shipment\ShipmentInterface $shipment): bool
    {
        return Shipment\ShipmentStates::isDeletableState($shipment->getState());
    }

    /**
     * Returns the platforms global actions.
     */
    public function getPlatformsGlobalActions(): array
    {
        return $this->getPlatformsActions(Gateway\PlatformActions::getGlobalActions());
    }

    /**
     * Returns the platforms mass actions.
     */
    public function getPlatformsMassActions(): array
    {
        return $this->getPlatformsActions(Gateway\PlatformActions::getMassActions());
    }

    /**
     * Returns the gateway shipment actions.
     */
    public function getGatewayShipmentActions(Shipment\ShipmentInterface $shipment): array
    {
        return $this->getGatewayActions($shipment, Gateway\GatewayActions::getShipmentActions());
    }

    /**
     * Returns the gateway api actions.
     */
    public function getGatewayApiActions(Shipment\ShipmentInterface $shipment): array
    {
        return $this->getGatewayActions($shipment, Gateway\GatewayActions::getApiActions());
    }

    /**
     * Returns the shipment platforms actions.
     */
    private function getPlatformsActions(array $filter): array
    {
        $platforms = [];

        foreach ($this->gatewayRegistry->getPlatformNames() as $name) {
            $platform = $this->gatewayRegistry->getPlatform($name);

            if (!empty($actions = array_intersect($platform->getActions(), $filter))) {
                $platforms[$name] = $actions;
            }
        }

        return $platforms;
    }

    /**
     * Returns the shipment gateway actions.
     */
    private function getGatewayActions(Shipment\ShipmentInterface $shipment, array $filter): array
    {
        $gateway = $this->gatewayRegistry->getGateway($shipment->getGatewayName());

        $actions = array_intersect($gateway->getActions(), $filter);

        return array_filter($actions, function ($action) use ($shipment, $gateway) {
            return $gateway->can($shipment, $action);
        });
    }
}
