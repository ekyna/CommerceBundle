<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Shipment;

use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Exception\ShipmentGatewayException;
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
    use Shipment\WeightCalculatorAwareTrait,
        Shipment\AddressResolverAwareTrait;

    /**
     * @var Gateway\RegistryInterface
     */
    private $gatewayRegistry;


    /**
     * Constructor.
     *
     * @param Gateway\RegistryInterface $gatewayRegistry
     */
    public function __construct(Gateway\RegistryInterface $gatewayRegistry)
    {
        $this->gatewayRegistry = $gatewayRegistry;
    }

    /**
     * Returns the gateway registry.
     *
     * @return Gateway\RegistryInterface
     */
    public function getGatewayRegistry(): Gateway\RegistryInterface
    {
        return $this->gatewayRegistry;
    }

    /**
     * Returns the shipment's total weight.
     *
     * @param Shipment\ShipmentInterface $shipment
     *
     * @return float
     */
    public function getShipmentWeight(Shipment\ShipmentInterface $shipment): float
    {
        if (0 < $shipment->getWeight()) {
            return $shipment->getWeight();
        }

        return $this->weightCalculator->calculateShipment($shipment);
    }

    /**
     * @inheritDoc
     */
    public function getCountryRepository(): CountryRepositoryInterface
    {
        return $this->addressResolver->getCountryRepository();
    }

    /**
     * @inheritdoc
     */
    public function resolveSenderAddress(
        Shipment\ShipmentInterface $shipment,
        bool $ignoreRelay = false
    ): AddressInterface {
        return $this->addressResolver->resolveSenderAddress($shipment, $ignoreRelay);
    }

    /**
     * @inheritdoc
     */
    public function resolveReceiverAddress(
        Shipment\ShipmentInterface $shipment,
        bool $ignoreRelay = false
    ): AddressInterface {
        return $this->addressResolver->resolveReceiverAddress($shipment, $ignoreRelay);
    }

    /**
     * Returns the tracking url.
     *
     * @param Shipment\ShipmentDataInterface $shipmentData
     *
     * @return string|null
     */
    public function getTrackingUrl(Shipment\ShipmentDataInterface $shipmentData): ?string
    {
        if ($shipmentData instanceof Shipment\ShipmentInterface) {
            $shipment = $shipmentData;
        } elseif ($shipmentData instanceof Shipment\ShipmentParcelInterface) {
            $shipment = $shipmentData->getShipment();
        } else {
            throw new InvalidArgumentException(
                "Expected instance of " . Shipment\ShipmentInterface::class .
                " or " . Shipment\ShipmentParcelInterface::class
            );
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
     *
     * @param Shipment\ShipmentDataInterface $shipmentData
     *
     * @return string|string
     */
    public function getProofUrl(Shipment\ShipmentDataInterface $shipmentData): ?string
    {
        if ($shipmentData instanceof Shipment\ShipmentInterface) {
            $shipment = $shipmentData;
        } elseif ($shipmentData instanceof Shipment\ShipmentParcelInterface) {
            $shipment = $shipmentData->getShipment();
        } else {
            throw new InvalidArgumentException(
                "Expected instance of " . Shipment\ShipmentInterface::class .
                " or " . Shipment\ShipmentParcelInterface::class
            );
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
     * Returns whether or not the given shipment can be deleted.
     *
     * @param Shipment\ShipmentInterface $shipment
     *
     * @return bool
     */
    public function isShipmentDeleteable(Shipment\ShipmentInterface $shipment): bool
    {
        return Shipment\ShipmentStates::isDeletableState($shipment->getState());
    }

    /**
     * Returns the platforms global actions.
     *
     * @return array
     */
    public function getPlatformsGlobalActions(): array
    {
        return $this->getPlatformsActions(Gateway\PlatformActions::getGlobalActions());
    }

    /**
     * Returns the platforms mass actions.
     *
     * @return array
     */
    public function getPlatformsMassActions(): array
    {
        return $this->getPlatformsActions(Gateway\PlatformActions::getMassActions());
    }

    /**
     * Returns the gateway shipment actions.
     *
     * @param Shipment\ShipmentInterface $shipment
     *
     * @return array
     */
    public function getGatewayShipmentActions(Shipment\ShipmentInterface $shipment): array
    {
        return $this->getGatewayActions($shipment, Gateway\GatewayActions::getShipmentActions());
    }

    /**
     * Returns the gateway api actions.
     *
     * @param Shipment\ShipmentInterface $shipment
     *
     * @return array
     */
    public function getGatewayApiActions(Shipment\ShipmentInterface $shipment): array
    {
        return $this->getGatewayActions($shipment, Gateway\GatewayActions::getApiActions());
    }

    /**
     * Returns the shipment platforms actions.
     *
     * @param array $filter
     *
     * @return array
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
     *
     * @param Shipment\ShipmentInterface $shipment
     * @param array                      $filter
     *
     * @return array
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
