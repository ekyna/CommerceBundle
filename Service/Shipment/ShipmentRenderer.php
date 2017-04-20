<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Shipment;

use Ekyna\Bundle\CommerceBundle\Action\Admin\Shipment\GatewayAction;
use Ekyna\Bundle\CommerceBundle\Model\ShipmentGatewayActions;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Shipment\Model;
use Twig\Environment;

use function sprintf;

/**
 * Class ShipmentRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Shipment
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ShipmentRenderer
{
    private PriceListBuilder $priceListBuilder;
    private ShipmentHelper   $shipmentHelper;
    private Environment      $twig;
    private string           $priceTemplate;


    public function __construct(
        PriceListBuilder $priceListBuilder,
        ShipmentHelper $shipmentHelper,
        Environment $twig,
        string $template = '@EkynaCommerce/Admin/ShipmentPrice/list.html.twig'
    ) {
        $this->priceListBuilder = $priceListBuilder;
        $this->shipmentHelper = $shipmentHelper;
        $this->twig = $twig;
        $this->priceTemplate = $template;
    }

    /**
     * Renders the shipment tracking buttons.
     */
    public function renderShipmentTracking(Model\ShipmentDataInterface $shipment): string
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
     */
    public function getGatewayButtons(Model\ShipmentInterface $shipment): array
    {
        $actions = $this->shipmentHelper->getGatewayShipmentActions($shipment);

        if (empty($actions)) {
            return [];
        }

        $buttons = [];

        foreach ($actions as $action) {
            // TODO Check permission (EDIT)
            // TODO Refactor
            /** @see \Ekyna\Bundle\CommerceBundle\Table\Column\ShipmentActionsType::buildCellView */

            $buttons[] = [
                'label'      => ShipmentGatewayActions::getLabel($action),
                'icon'       => ShipmentGatewayActions::getIcon($action),
                'theme'      => ShipmentGatewayActions::getTheme($action),
                'confirm'    => ShipmentGatewayActions::getConfirm($action),
                'target'     => ShipmentGatewayActions::getTarget($action),
                'action'     => GatewayAction::class,
                'parameters' => ['action' => $action],
            ];
        }

        return $buttons;
    }

    /**
     * Renders the price list.
     *
     * @param Model\ShipmentMethodInterface|Model\ShipmentZoneInterface $source
     *
     * @TODO PHP8 union types
     */
    public function renderShipmentPrices($source): string
    {
        if ($source instanceof Model\ShipmentMethodInterface) {
            $list = $this->priceListBuilder->buildByMethod($source);
        } elseif ($source instanceof Model\ShipmentZoneInterface) {
            $list = $this->priceListBuilder->buildByZone($source);
        } else {
            throw new UnexpectedTypeException($source, [
                Model\ShipmentMethodInterface::class,
                Model\ShipmentZoneInterface::class,
            ]);
        }

        return $this->twig->render($this->priceTemplate, [
            'list' => $list,
        ]);
    }
}
