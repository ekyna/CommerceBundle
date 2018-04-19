<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\CoreBundle\Controller\Controller;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ShipmentPlatformController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentPlatformController extends Controller
{
    /**
     * Shipment platform export action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportAction(Request $request)
    {
        // TODO

        return new Response('Not yet implemented.');
    }

    /**
     * Shipment platform import action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function importAction(Request $request)
    {
        // TODO

        return new Response('Not yet implemented.');
    }

    /**
     * Returns the shipment platform for the given name.
     *
     * @param string $name
     *
     * @return \Ekyna\Component\Commerce\Shipment\Gateway\PlatformInterface
     */
    protected function getPlatform($name)
    {
        try {
            return $this
                ->get('ekyna_commerce.shipment.gateway_registry')
                ->getPlatform($name);
        } catch (CommerceExceptionInterface $e) {
            throw $this->createNotFoundException("Platform '$name' not found.'");
        }
    }
}
