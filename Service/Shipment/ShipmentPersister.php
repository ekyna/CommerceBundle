<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Shipment;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\PersisterInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

/**
 * Class ShipmentPersister
 * @package Ekyna\Bundle\CommerceBundle\Service\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentPersister implements PersisterInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var bool
     */
    private $pendingFlush = false;


    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @inheritDoc
     */
    public function persist(ShipmentInterface $shipment)
    {
        $this->entityManager->persist($shipment);

        $this->pendingFlush = true;
    }

    /**
     * @inheritDoc
     */
    public function flush()
    {
        if (!$this->pendingFlush) {
            return;
        }

        $this->entityManager->flush();

        $this->pendingFlush = false;
    }
}
