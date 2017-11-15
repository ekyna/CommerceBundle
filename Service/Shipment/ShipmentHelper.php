<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Shipment;

use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Shipment\Gateway\Action;
use Ekyna\Component\Commerce\Shipment\Gateway\RegistryInterface;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentAddressResolverInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

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
     * @var RegistryInterface
     */
    private $gatewayRegistry;

    /**
     * @var RequestStack
     */
    private $requestStack;


    /**
     * Constructor.
     *
     * @param RegistryInterface $gatewayRegistry
     * @param RequestStack      $requestStack
     */
    public function __construct(
        RegistryInterface $gatewayRegistry,
        RequestStack $requestStack
    ) {
        $this->gatewayRegistry = $gatewayRegistry;
        $this->requestStack = $requestStack;
    }

    /**
     * Returns the gateway registry.
     *
     * @return RegistryInterface
     */
    public function getGatewayRegistry()
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
    public function getShipmentWeight(Shipment\ShipmentInterface $shipment)
    {
        if (0 < $shipment->getWeight()) {
            return $shipment->getWeight();
        }

        return $this->weightCalculator->calculateShipment($shipment);
    }

    /**
     * @inheritdoc
     */
    public function resolveSenderAddress(ShipmentInterface $shipment)
    {
        return $this->addressResolver->resolveSenderAddress($shipment);
    }

    /**
     * @inheritdoc
     */
    public function resolveReceiverAddress(ShipmentInterface $shipment)
    {
        return $this->addressResolver->resolveReceiverAddress($shipment);
    }

    /**
     * Returns whether or not the given shipment can be deleted.
     *
     * @param Shipment\ShipmentInterface $shipment
     *
     * @return bool
     */
    public function isShipmentDeleteable(Shipment\ShipmentInterface $shipment)
    {
        return Shipment\ShipmentStates::isDeletableState($shipment->getState());
    }

    /**
     * Executes the platform action.
     *
     * @param string                           $platformName
     * @param string                           $actionName
     * @param array|Shipment\ShipmentInterface $shipments
     * @param Request|null                     $sfRequest
     *
     * @return \Symfony\Component\HttpFoundation\Response|null
     *
     * @throws LogicException
     */
    public function executePlatformAction($platformName, $actionName, $shipments, Request $sfRequest = null)
    {
        $platform = $this->gatewayRegistry->getPlatform($platformName);

        $action = $this->createAction($actionName, $shipments, $sfRequest);

        if (!$platform->supports($action)) {
            throw new LogicException("Unsupported action.");
        }

        try {
            $psrResponse = $platform->execute($action);
        } catch (\Exception $e) {
            throw new RuntimeException($e->getMessage());
        }

        if (null !== $psrResponse) {
            return (new HttpFoundationFactory())->createResponse($psrResponse);
        }

        return null;
    }

    /**
     * Executes the gateway action.
     *
     * @param string                           $gatewayName
     * @param string                           $actionName
     * @param array|Shipment\ShipmentInterface $shipments
     * @param Request|null                     $sfRequest
     *
     * @return \Symfony\Component\HttpFoundation\Response|null
     *
     * @throws LogicException
     */
    public function executeGatewayAction($gatewayName, $actionName, $shipments, Request $sfRequest = null)
    {
        $gateway = $this->gatewayRegistry->getGateway($gatewayName);

        $action = $this->createAction($actionName, $shipments, $sfRequest);

        if (!$gateway->supports($action)) {
            throw new LogicException("Unsupported action.");
        }

        try {
            $psrResponse = $gateway->execute($action);
        } catch (\Exception $e) {
            throw new RuntimeException($e->getMessage());
        }

        if (null !== $psrResponse) {
            return (new HttpFoundationFactory())->createResponse($psrResponse);
        }

        return null;
    }

    /**
     * Creates the shipment action.
     *
     * @param string                           $name
     * @param array|Shipment\ShipmentInterface $shipments
     * @param Request                          $sfRequest
     *
     * @return Action\ActionInterface
     */
    public function createAction($name, $shipments, Request $sfRequest = null)
    {
        $class = $this->getActionClass($name);

        if (null === $sfRequest) {
            $sfRequest = $this->requestStack->getMasterRequest();
        }

        $psrRequest = (new DiactorosFactory())->createRequest($sfRequest);

        $shipments = is_array($shipments) ? $shipments : [$shipments];

        return new $class($psrRequest, $shipments);
    }

    /**
     * Returns the shipment platforms actions.
     *
     * @param string $scope
     *
     * @return array
     */
    public function getPlatformsActions($scope = null)
    {
        $actions = [];

        $platformNames = $this->gatewayRegistry->getPlatformNames();

        foreach ($platformNames as $name) {
            $platform = $this->gatewayRegistry->getPlatform($name);

            $classes = [];

            foreach ($platform->getActions() as $class) {
                if ($scope && !in_array($scope, $this->getActionScopes($class))) {
                    continue;
                }

                $classes[] = $class;
            }

            if (!empty($classes)) {
                $actions[$name] = $classes;
            }
        }

        return $actions;
    }

    /**
     * Returns the shipment platforms actions names.
     *
     * @param string $scope
     *
     * @return array
     */
    public function getPlatformsActionsNames($scope = null)
    {
        $platforms = $this->getPlatformsActions($scope);

        foreach ($platforms as $name => &$actions) {
            $actions = array_map([$this, 'getActionName'], $actions);
        }

        return $platforms;
    }

    /**
     * Returns the shipment gateway actions.
     *
     * @param Shipment\ShipmentInterface $shipment
     * @param string                     $scope
     *
     * @return array
     */
    public function getGatewayActions(Shipment\ShipmentInterface $shipment = null, $scope = null)
    {
        $gateway = $this->gatewayRegistry->getGateway($shipment->getGatewayName());

        $actions = [];

        foreach ($gateway->getActions($shipment) as $class) {
            if ($scope && !in_array($scope, $this->getActionScopes($class))) {
                continue;
            }

            $actions[] = $class;
        }

        return $actions;
    }

    /**
     * Returns the shipment gateway actions names.
     *
     * @param Shipment\ShipmentInterface $shipment
     * @param string                     $scope
     *
     * @return array
     */
    public function getGatewayActionsNames(Shipment\ShipmentInterface $shipment = null, $scope = null)
    {
        return array_map([$this, 'getActionName'], $this->getGatewayActions($shipment, $scope));
    }

    /**
     * Returns the action (translation) label.
     *
     * @param string $classOrName
     *
     * @return string
     */
    public function getActionLabel($classOrName)
    {
        $name = class_exists($classOrName) ? $this->getActionName($classOrName) : $classOrName;

        return 'ekyna_commerce.shipment.action.' . $name;
    }

    /**
     * Returns the action class for the given name.
     *
     * @param string $name
     *
     * @return string
     */
    public function getActionClass($name)
    {
        switch ($name) {
            case 'cancel' :
                return Action\Cancel::class;
            case 'capture' :
                return Action\Capture::class;
            case 'export' :
                return Action\Export::class;
            case 'import' :
                return Action\Import::class;
            case 'print_label' :
                return Action\PrintLabel::class;
            case 'ship' :
                return Action\Ship::class;
        }

        throw new InvalidArgumentException(sprintf("Unexpected shipment action name '%s'.", $name));
    }

    /**
     * Returns the action scopes.
     *
     * @param string $class
     *
     * @return array
     */
    private function getActionScopes($class)
    {
        return call_user_func([$class, 'getScopes']);
    }

    /**
     * Returns the action name.
     *
     * @param string $class
     *
     * @return string
     */
    private function getActionName($class)
    {
        return call_user_func([$class, 'getName']);
    }

    /**
     * Returns the action icon.
     *
     * @param string $name
     *
     * @return string|string
     */
    public function getActionIcon($name)
    {
        switch ($name) {
            case Action\PrintLabel::NAME :
                return 'barcode';
            case Action\Cancel::NAME :
                return 'remove';
            case Action\Ship::NAME :
                return 'road';
        }

        return null;
    }
}
