<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Component\Commerce\Shipment\Entity\ShipmentPrice;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentZoneInterface;

/**
 * Class ShipmentPriceList
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentPriceList
{
    /**
     * @var string
     */
    private $filterBy;

    /**
     * @var ShipmentMethodInterface[]|ShipmentZoneInterface[]
     */
    private $filters;

    /**
     * @var ShipmentPrice[]
     */
    private $prices;


    /**
     * Constructor.
     *
     * @param string $filterBy
     * @param array  $filters
     * @param array  $prices
     */
    public function __construct($filterBy, array $filters, array $prices)
    {
        $this->filterBy = $filterBy;
        $this->filters = $filters;
        $this->prices = $prices;
    }



    /**
     * Returns the filters.
     *
     * @return \Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface[]|\Ekyna\Component\Commerce\Shipment\Model\ShipmentZoneInterface[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Returns the prices.
     *
     * @return \Ekyna\Component\Commerce\Shipment\Entity\ShipmentPrice[]
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * Returns the filterBy.
     *
     * @return string
     */
    public function getFilterBy()
    {
        return $this->filterBy;
    }
}
