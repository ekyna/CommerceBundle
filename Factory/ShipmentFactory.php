<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Factory;

use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Factory\ResourceFactory;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class ShipmentFactory
 * @package Ekyna\Bundle\CommerceBundle\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ShipmentFactory extends ResourceFactory
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function create(): ResourceInterface
    {
        /** @var ShipmentInterface $shipment */
        $shipment = parent::create();

        if (null === $request = $this->requestStack->getMainRequest()) {
            return $shipment;
        }

        $shipment->setReturn($request->query->getBoolean('return'));

        if ($shipment->isReturn()) {
            $shipment->setAutoInvoice(false);
        }

        return $shipment;
    }
}
