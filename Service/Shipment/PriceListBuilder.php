<?php

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
    /**
     * @var Repository\ShipmentZoneRepositoryInterface
     */
    private $zoneRepository;

    /**
     * @var Repository\ShipmentMethodRepositoryInterface
     */
    private $methodRepository;

    /**
     * @var Repository\ShipmentPriceRepositoryInterface
     */
    private $priceRepository;


    /**
     * Constructor.
     *
     * @param Repository\ShipmentZoneRepositoryInterface   $zoneRepository
     * @param Repository\ShipmentMethodRepositoryInterface $methodRepository
     * @param Repository\ShipmentPriceRepositoryInterface  $priceRepository
     */
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
     *
     * @param Model\ShipmentZoneInterface $zone
     *
     * @return string
     */
    public function buildByZone(Model\ShipmentZoneInterface $zone)
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
     *
     * @param Model\ShipmentMethodInterface $method
     *
     * @return string
     */
    public function buildByMethod(Model\ShipmentMethodInterface $method)
    {
        $filters = $this->zoneRepository->findHavingPrices($method);

        $prices = $this->priceRepository->findBy(
            ['method' => $method],
            ['zone' => 'ASC', 'weight' => 'ASC']
        );

        return new ShipmentPriceList('zone', $filters, $prices);
    }
}
