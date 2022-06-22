<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Bundle\CommerceBundle\Service\Shipment\ShipmentHelper;
use Ekyna\Bundle\CommerceBundle\Service\Shipment\ShipmentRenderer;
use Ekyna\Component\Commerce\Shipment\Model;
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
    public function getFilters(): array
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
                'shipment_weight',
                [ShipmentHelper::class, 'getShipmentWeight']
            ),
            new TwigFilter(
                'shipment_deleteable',
                [ShipmentHelper::class, 'isShipmentDeleteable']
            ),
            new TwigFilter(
                'shipment_sender_address',
                [ShipmentHelper::class, 'resolveSenderAddress']
            ),
            new TwigFilter(
                'shipment_receiver_address',
                [ShipmentHelper::class, 'resolveReceiverAddress']
            ),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'display_shipment_prices',
                [ShipmentRenderer::class, 'renderShipmentPrices'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'display_shipment_tracking',
                [ShipmentRenderer::class, 'renderShipmentTracking'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'shipment_platform_global_actions',
                [ShipmentHelper::class, 'getPlatformsGlobalActions']
            ),
            new TwigFunction(
                'shipment_gateway_buttons',
                [ShipmentRenderer::class, 'getGatewayButtons']
            ),
        ];
    }

    public function getTests(): array
    {
        return [
            new TwigTest('shipment', function ($subject) {
                return $subject instanceof Model\ShipmentInterface;
            }),
            new TwigTest('shipment_data', function ($subject) {
                return $subject instanceof Model\ShipmentDataInterface;
            }),
            new TwigTest('shipment_subject', function ($subject) {
                return $subject instanceof Model\ShipmentSubjectInterface;
            }),
        ];
    }
}
