<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayRegistryInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\PlatformInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ShipmentPlatformController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentPlatformController
{
    private GatewayRegistryInterface $gatewayRegistry;

    public function __construct(GatewayRegistryInterface $gatewayRegistry)
    {
        $this->gatewayRegistry = $gatewayRegistry;
    }

    /**
     * Shipment platform execute action.
     */
    public function action(Request $request): Response
    {
        // TODO

        /*$platform = $this->getPlatform($request->attributes->getAlnum('name'));

        $actionName = $request->attributes->get('action');

        $shipments = [];

        try {
            $response = $platform->e($platformName, $actionName, $shipments);

            if (null !== $response) {
                return $response;
            }
        } catch (Exception\ShipmentGatewayException $e) {
            if ($this->getParameter('kernel.debug')) {
                throw $e;
            }

            $this->addFlash($e->getMessage(), 'danger');
        }

        if (!empty($referer = $request->headers->get('referer'))) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('admin_ekyna_commerce_list_order_shipment');*/

        return new Response('Not yet implemented.');
    }

    /**
     * Shipment platform export action.
     */
    public function export(Request $request): Response
    {
        // TODO

        return new Response('Not yet implemented.');
    }

    /**
     * Shipment platform import action.
     */
    public function import(Request $request): Response
    {
        // TODO

        return new Response('Not yet implemented.');
    }

    /**
     * Returns the shipment platform for the given name.
     */
    protected function getPlatform(string $name): PlatformInterface
    {
        try {
            return $this
                ->gatewayRegistry
                ->getPlatform($name);
        } catch (CommerceExceptionInterface $e) {
            throw new NotFoundHttpException("Platform '$name' not found.'");
        }
    }
}
