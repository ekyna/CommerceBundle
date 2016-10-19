<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Model\ShipmentMethodInterface;
use Ekyna\Bundle\CommerceBundle\Service\ConstantHelper;
use Ekyna\Bundle\CommerceBundle\Service\Shipment\PriceRenderer;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentZoneInterface;

/**
 * Class ShipmentExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentExtension extends \Twig_Extension
{
    /**
     * @var ConstantHelper
     */
    private $constantHelper;

    /**
     * @var PriceRenderer
     */
    private $priceRenderer;


    /**
     * Constructor.
     *
     * @param ConstantHelper $constantHelper
     * @param PriceRenderer  $priceRenderer
     */
    public function __construct(
        ConstantHelper $constantHelper,
        PriceRenderer  $priceRenderer
    ) {
        $this->constantHelper = $constantHelper;
        $this->priceRenderer = $priceRenderer;
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'shipment_state_label',
                [$this->constantHelper, 'renderShipmentStateLabel'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'shipment_state_badge',
                [$this->constantHelper, 'renderShipmentStateBadge'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'display_shipment_prices',
                [$this, 'renderShipmentPrices'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * Renders the price list.
     *
     * @param ShipmentMethodInterface|ShipmentZoneInterface $source
     *
     * @return string
     */
    public function renderShipmentPrices($source)
    {
        if ($source instanceof ShipmentMethodInterface) {
            return $this->priceRenderer->renderByMethod($source);
        } elseif ($source instanceof ShipmentZoneInterface) {
            return $this->priceRenderer->renderByZone($source);
        }

        throw new InvalidArgumentException(
            "Expected instance of ShipmentMethodInterface or ShipmentZoneInterface."
        );
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'ekyna_commerce_shipment';
    }
}
