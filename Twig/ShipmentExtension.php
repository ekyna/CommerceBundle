<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Model\ShipmentMethodInterface;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Bundle\CommerceBundle\Service\Shipment\PriceListBuilder;
use Ekyna\Bundle\CommerceBundle\Service\Shipment\ShipmentHelper;
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
     * @var ConstantsHelper
     */
    private $constantHelper;

    /**
     * @var PriceListBuilder
     */
    private $priceListBuilder;

    /**
     * @var ShipmentHelper
     */
    private $shipmentHelper;

    /**
     * @var \Twig_Template
     */
    private $template;


    /**
     * Constructor.
     *
     * @param ConstantsHelper  $constantHelper
     * @param PriceListBuilder $priceListBuilder
     * @param ShipmentHelper   $shipmentHelper
     * @param string           $template
     */
    public function __construct(
        ConstantsHelper $constantHelper,
        PriceListBuilder $priceListBuilder,
        ShipmentHelper   $shipmentHelper,
        $template = 'EkynaCommerceBundle:Admin/ShipmentPrice:list.html.twig'
    ) {
        $this->constantHelper = $constantHelper;
        $this->priceListBuilder = $priceListBuilder;
        $this->shipmentHelper = $shipmentHelper;
        $this->template = $template;
    }

    /**
     * @inheritdoc
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->template = $environment->loadTemplate($this->template);
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
            new \Twig_SimpleFilter(
                'shipment_action_label',
                [$this->shipmentHelper, 'getActionLabel'],
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
            new \Twig_SimpleFunction(
                'shipment_platforms_actions',
                [$this->shipmentHelper, 'getPlatformsActionsNames']
            ),
            new \Twig_SimpleFunction(
                'shipment_gateway_actions',
                [$this->shipmentHelper, 'getGatewayActionsNames']
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
            $list = $this->priceListBuilder->buildByMethod($source);
        } elseif ($source instanceof ShipmentZoneInterface) {
            $list = $this->priceListBuilder->buildByZone($source);
        } else {
            throw new InvalidArgumentException(
                "Expected instance of ShipmentMethodInterface or ShipmentZoneInterface."
            );
        }

        return $this->template->render([
            'list' => $list,
        ]);
    }


    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'ekyna_commerce_shipment';
    }
}
