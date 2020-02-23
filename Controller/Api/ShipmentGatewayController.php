<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Api;

use Ekyna\Bundle\CoreBundle\Controller\Controller;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Commerce\Exception\ShipmentGatewayException;
use Ekyna\Component\Commerce\Shipment\Gateway\Model\Address;
use Ekyna\Component\Commerce\Shipment\Gateway\RegistryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ShipmentGatewayController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Api
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentGatewayController extends Controller
{
    /**
     * @var RegistryInterface
     */
    private $gatewayRegistry;


    /**
     * Constructor.
     *
     * @param RegistryInterface $gatewayRegistry
     */
    public function __construct(RegistryInterface $gatewayRegistry)
    {
        $this->gatewayRegistry = $gatewayRegistry;
    }

    /**
     * List relay points action.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listRelayPoints(Request $request): JsonResponse
    {
        $gateway = $this->getGateway($request->attributes->get('gateway'));

        $weight = $request->query->get('weight', 1); // TODO default

        $address = new Address();
        $address
            ->setStreet($request->query->get('street'))
            ->setPostalCode($request->query->get('postalCode'))
            ->setCity($request->query->get('city'));
            // TODO ->setCountry()

        try {
            $response = $gateway->listRelayPoints($address, $weight);
            $data = $this->get('serializer')->serialize([
                'relay_points' => $response->getRelayPoints()
            ], 'json', ['groups' => ['Default']]);
        } catch (ShipmentGatewayException $e) {
            $data = json_encode([
                'relay_points' => [],
                'error'        => $e->getMessage(),
            ]);
        }

        return new JsonResponse($data, JsonResponse::HTTP_OK, [], true);
    }

    /**
     * Get relay point action.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getRelayPoint(Request $request): JsonResponse
    {
        $gateway = $this->getGateway($request->attributes->get('gateway'));
        $number = $request->query->get('number');

        $relayPoint = $this
            ->get('ekyna_commerce.relay_point.repository')
            ->findOneByNumberAndPlatform($number, $gateway->getPlatform()->getName());

        if (null === $relayPoint) {
            $response = $gateway->getRelayPoint($number);

            if (null !== $relayPoint = $response->getRelayPoint()) {
                $em = $this->get('ekyna_commerce.relay_point.manager');
                $em->persist($relayPoint);
                $em->flush();
            }
        }

        $data = $this->get('serializer')->serialize([
            'relay_point' => $relayPoint
        ], 'json', ['groups' => ['Default']]);

        // TODO caching

        return new JsonResponse($data, JsonResponse::HTTP_OK, [], true);
    }

    /**
     * Returns the shipment gateway for the given name.
     *
     * @param string $name
     *
     * @return \Ekyna\Component\Commerce\Shipment\Gateway\GatewayInterface
     */
    protected function getGateway($name)
    {
        try {
            return $this
                ->get('ekyna_commerce.shipment.gateway_registry')
                ->getGateway($name);
        } catch (CommerceExceptionInterface $e) {
            throw $this->createNotFoundException("Gateway '$name' not found.'");
        }
    }
}
