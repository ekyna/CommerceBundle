<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Api;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\Address;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Exception\ShipmentGatewayException;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayRegistryInterface;
use Ekyna\Component\Commerce\Shipment\Repository\RelayPointRepositoryInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class ShipmentGatewayController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Api
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentGatewayController
{
    private GatewayRegistryInterface      $gatewayRegistry;
    private RelayPointRepositoryInterface $relayPointRepository;
    private ResourceManagerInterface      $relayPointManager;
    private SerializerInterface           $serializer;

    public function __construct(
        GatewayRegistryInterface      $gatewayRegistry,
        RelayPointRepositoryInterface $relayPointRepository,
        ResourceManagerInterface      $relayPointManager,
        SerializerInterface           $serializer
    ) {
        $this->gatewayRegistry = $gatewayRegistry;
        $this->relayPointRepository = $relayPointRepository;
        $this->relayPointManager = $relayPointManager;
        $this->serializer = $serializer;
    }

    public function listRelayPoints(Request $request): Response
    {
        $gateway = $this->getGateway($request->attributes->get('gateway'));

        $weight = new Decimal($request->query->get('weight', 1)); // TODO default

        $address = new Address();
        $address
            ->setStreet($request->query->get('street'))
            ->setPostalCode($request->query->get('postalCode'))
            ->setCity($request->query->get('city'));
        // TODO ->setCountry()

        try {
            $response = $gateway->listRelayPoints($address, $weight);
            $data = $this->serializer->serialize([
                'relay_points' => $response->getRelayPoints(),
            ], 'json', ['groups' => ['Default']]);
        } catch (ShipmentGatewayException $e) {
            $data = json_encode([
                'relay_points' => [],
                'error'        => $e->getMessage(),
            ]);
        }

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    public function getRelayPoint(Request $request): Response
    {
        $gateway = $this->getGateway($request->attributes->get('gateway'));
        $number = $request->query->get('number');

        $relayPoint = $this
            ->relayPointRepository
            ->findOneByNumberAndPlatform($number, $gateway->getPlatform()->getName());

        if (null === $relayPoint) {
            $response = $gateway->getRelayPoint($number);

            if (null !== $relayPoint = $response->getRelayPoint()) {
                $event = $this->relayPointManager->save($relayPoint);

                if ($event->hasErrors()) {
                    throw new RuntimeException('Failed to create relay point.');
                }
            }
        }

        $data = $this->serializer->serialize([
            'relay_point' => $relayPoint,
        ], 'json', ['groups' => ['Default']]);

        // TODO caching

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    protected function getGateway(string $name): GatewayInterface
    {
        try {
            return $this
                ->gatewayRegistry
                ->getGateway($name);
        } catch (CommerceExceptionInterface $e) {
            throw new NotFoundHttpException("Gateway '$name' not found.'");
        }
    }
}
