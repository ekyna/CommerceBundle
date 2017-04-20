<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Shipment;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Exception\ShipmentGatewayException;
use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Shipment\Gateway\PersisterInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

/**
 * Class ShipmentPersister
 * @package Ekyna\Bundle\CommerceBundle\Service\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentPersister implements PersisterInterface
{
    private EntityManagerInterface $entityManager;
    private bool                   $pendingFlush = false;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function persist(ShipmentInterface $shipment): void
    {
        $this->entityManager->persist($shipment);

        $this->pendingFlush = true;
    }

    public function flush(): void
    {
        if (!$this->pendingFlush) {
            return;
        }

        try {
            $this->entityManager->flush();
        } catch (StockLogicException $e) {
            // TODO Report error by email

            throw new ShipmentGatewayException($e->getMessage(), $e->getCode(), $e);
        }

        $this->pendingFlush = false;
    }
}
