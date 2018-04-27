<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Model\ShipmentGatewayActions;
use Ekyna\Bundle\CommerceBundle\Model\ShipmentMethodInterface;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Bundle\CommerceBundle\Service\Shipment\PriceListBuilder;
use Ekyna\Bundle\CommerceBundle\Service\Shipment\ShipmentHelper;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentDataInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface;
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
        ShipmentHelper $shipmentHelper,
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
            new \Twig_SimpleFilter(
                'shipment_weight',
                [$this->shipmentHelper, 'getShipmentWeight']
            ),
            new \Twig_SimpleFilter(
                'shipment_deleteable',
                [$this->shipmentHelper, 'isShipmentDeleteable']
            ),
            new \Twig_SimpleFilter(
                'shipment_sender_address',
                [$this->shipmentHelper, 'resolveSenderAddress']
            ),
            new \Twig_SimpleFilter(
                'shipment_receiver_address',
                [$this->shipmentHelper, 'resolveReceiverAddress']
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
                'display_shipment_tracking',
                [$this, 'renderShipmentTracking'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFunction(
                'shipment_platform_global_actions',
                [$this->shipmentHelper, 'getPlatformsGlobalActions']
            ),
            new \Twig_SimpleFunction(
                'shipment_gateway_buttons',
                [$this, 'getGatewayButtons']
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getTests()
    {
        return [
            new \Twig_SimpleTest('shipment_subject', function ($subject) {
                return $subject instanceof ShipmentSubjectInterface;
            }),
            new \Twig_SimpleTest('shipment_partial', function ($subject) {
                return $subject instanceof ShipmentSubjectInterface;
            }),
        ];
    }

    /**
     * Renders the shipment tracking buttons.
     *
     * @param ShipmentDataInterface $shipment
     *
     * @return string
     */
    public function renderShipmentTracking(ShipmentDataInterface $shipment)
    {
        if (empty($number = $shipment->getTrackingNumber())) {
            return '';
        }

        $output = $number;

        if (null !== $url = $this->shipmentHelper->getTrackingUrl($shipment)) {
            /** @noinspection HtmlUnknownTarget */
            $output = sprintf('<a href="%s" target="_blank"><i class="fa fa-map-marker"></i> %s</a>', $url, $number);

            if (null !== $url = $this->shipmentHelper->getProofUrl($shipment)) {
                /** @noinspection HtmlUnknownTarget */
                $output .= sprintf('&nbsp;&nbsp;<a href="%s" target="_blank"><i class="fa fa-check-square-o"></i></a>', $url);
            }
        }

        return $output;
    }

    /**
     * Returns the shipment gateway action buttons.
     *
     * @param ShipmentInterface $shipment
     *
     * @return array
     */
    public function getGatewayButtons(ShipmentInterface $shipment)
    {
        $actions = $this->shipmentHelper->getGatewayShipmentActions($shipment);

        if (empty($actions)) {
            return [];
        }

        $buttons = [];

        foreach ($actions as $action) {
            // TODO Check permission (EDIT)
            // TODO Refactor
            /** @see \Ekyna\Bundle\CommerceBundle\Table\Column\ShipmentActionsType */

            $buttons[] = [
                'label'      => ShipmentGatewayActions::getLabel($action),
                'icon'       => ShipmentGatewayActions::getIcon($action),
                'theme'      => ShipmentGatewayActions::getTheme($action),
                'confirm'    => ShipmentGatewayActions::getConfirm($action),
                'target'     => ShipmentGatewayActions::getTarget($action),
                'route'      => 'ekyna_commerce_order_shipment_admin_' . $action,
                'parameters' => [
                    'orderId'         => $shipment->getSale()->getId(),
                    'orderShipmentId' => $shipment->getId(),
                ],
            ];
        }

        return $buttons;
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
}
