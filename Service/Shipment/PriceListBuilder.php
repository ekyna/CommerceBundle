<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Shipment;

use Ekyna\Bundle\CommerceBundle\Model\ShipmentPriceList;
use Ekyna\Component\Commerce\Shipment\Model;
use Ekyna\Component\Commerce\Shipment\Repository;

/**
 * Class PriceListBuilder
 * @package Ekyna\Bundle\CommerceBundle\Service\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceListBuilder
{
    private Repository\ShipmentZoneRepositoryInterface $zoneRepository;
    private Repository\ShipmentMethodRepositoryInterface $methodRepository;
    private Repository\ShipmentPriceRepositoryInterface $priceRepository;


    public function __construct(
        Repository\ShipmentZoneRepositoryInterface $zoneRepository,
        Repository\ShipmentMethodRepositoryInterface $methodRepository,
        Repository\ShipmentPriceRepositoryInterface $priceRepository
    ) {
        $this->zoneRepository = $zoneRepository;
        $this->methodRepository = $methodRepository;
        $this->priceRepository = $priceRepository;
    }

    /**
     * Builds the price list by zone.
     */
    public function buildByZone(Model\ShipmentZoneInterface $zone): ShipmentPriceList
    {
        $filters = $this->methodRepository->findHavingPrices($zone);

        $prices = $this->priceRepository->findBy(
            ['zone' => $zone],
            ['method' => 'ASC', 'weight' => 'ASC']
        );

        return new ShipmentPriceList('method', $filters, $prices);
    }

    /**
     * Builds the price list by method.
     */
    public function buildByMethod(Model\ShipmentMethodInterface $method): ShipmentPriceList
    {
        $filters = $this->zoneRepository->findHavingPrices($method);

        $prices = $this->priceRepository->findBy(
            ['method' => $method],
            ['zone' => 'ASC', 'weight' => 'ASC']
        );

        return new ShipmentPriceList('zone', $filters, $prices);
    }
}
