<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Shipment;

use Ekyna\Component\Commerce\Shipment\Model;
use Ekyna\Component\Commerce\Shipment\Repository;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/**
 * Class PriceRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceRenderer
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
     * @var EngineInterface
     */
    private $engine;

    /**
     * @var \Twig_Template
     */
    private $template;


    /**
     * Constructor.
     *
     * @param Repository\ShipmentZoneRepositoryInterface   $zoneRepository
     * @param Repository\ShipmentMethodRepositoryInterface $methodRepository
     * @param Repository\ShipmentPriceRepositoryInterface  $priceRepository
     * @param EngineInterface                              $engine
     * @param string                                       $template
     */
    public function __construct(
        Repository\ShipmentZoneRepositoryInterface $zoneRepository,
        Repository\ShipmentMethodRepositoryInterface $methodRepository,
        Repository\ShipmentPriceRepositoryInterface $priceRepository,
        EngineInterface $engine,
        $template
    ) {
        $this->zoneRepository = $zoneRepository;
        $this->methodRepository = $methodRepository;
        $this->priceRepository = $priceRepository;
        $this->engine = $engine;
        $this->template = $template;
    }

    /**
     * Renders the price list by zone.
     *
     * @param Model\ShipmentZoneInterface $zone
     *
     * @return string
     */
    public function renderByZone(Model\ShipmentZoneInterface $zone)
    {
        $filters = $this->methodRepository->findAll();

        $prices = $this->priceRepository->findBy(
            ['zone' => $zone],
            ['method' => 'ASC', 'weight' => 'ASC']
        );

        return $this->engine->render(
            $this->template,
            [
                'filter_by' => 'method',
                'filters'   => $filters,
                'prices'    => $prices,
            ]
        );
    }

    /**
     * Renders the price list by method.
     *
     * @param Model\ShipmentMethodInterface $method
     *
     * @return string
     */
    public function renderByMethod(Model\ShipmentMethodInterface $method)
    {
        $filters = $this->zoneRepository->findAll();

        $prices = $this->priceRepository->findBy(
            ['method' => $method],
            ['zone' => 'ASC', 'weight' => 'ASC']
        );

        return $this->engine->render(
            $this->template,
            [
                'filter_by' => 'zone',
                'filters'   => $filters,
                'prices'    => $prices,
            ]
        );
    }
}
