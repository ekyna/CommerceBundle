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
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

/**
 * Class ShipmentExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentExtension extends AbstractExtension
{
    /**
     * @var PriceListBuilder
     */
    private $priceListBuilder;

    /**
     * @var ShipmentHelper
     */
    private $shipmentHelper;

    /**
     * @var string
     */
    private $priceTemplate;


    /**
     * Constructor.
     *
     * @param PriceListBuilder $priceListBuilder
     * @param ShipmentHelper   $shipmentHelper
     * @param string           $template
     */
    public function __construct(
        PriceListBuilder $priceListBuilder,
        ShipmentHelper $shipmentHelper,
        $template = '@EkynaCommerce/Admin/ShipmentPrice/list.html.twig'
    ) {
        $this->priceListBuilder = $priceListBuilder;
        $this->shipmentHelper = $shipmentHelper;
        $this->priceTemplate = $template;
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new TwigFilter(
                'shipment_state_label',
                [ConstantsHelper::class, 'renderShipmentStateLabel'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'shipment_state_badge',
                [ConstantsHelper::class, 'renderShipmentStateBadge'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'shipment_action_label',
                [$this->shipmentHelper, 'getActionLabel'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'shipment_weight',
                [$this->shipmentHelper, 'getShipmentWeight']
            ),
            new TwigFilter(
                'shipment_deleteable',
                [$this->shipmentHelper, 'isShipmentDeleteable']
            ),
            new TwigFilter(
                'shipment_sender_address',
                [$this->shipmentHelper, 'resolveSenderAddress']
            ),
            new TwigFilter(
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
            new TwigFunction(
                'display_shipment_prices',
                [$this, 'renderShipmentPrices'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new TwigFunction(
                'display_shipment_tracking',
                [$this, 'renderShipmentTracking'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'shipment_platform_global_actions',
                [$this->shipmentHelper, 'getPlatformsGlobalActions']
            ),
            new TwigFunction(
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
            new TwigTest('shipment_subject', function ($subject) {
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
                $output .= sprintf('&nbsp;&nbsp;<a href="%s" target="_blank"><i class="fa fa-check-square-o"></i></a>',
                    $url);
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
     * @param Environment                                   $env
     * @param ShipmentMethodInterface|ShipmentZoneInterface $source
     *
     * @return string
     */
    public function renderShipmentPrices(Environment $env, $source)
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

        return $env->render($this->priceTemplate, [
            'list' => $list,
        ]);
    }
}
