<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Shipment;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\CommerceBundle\Service\Shipment\LabelRenderer;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\RoutingActionInterface;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Component\Commerce\Exception\ShipmentGatewayException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayActions;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayRegistryInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\PersisterInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Exception\PdfException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;

use function call_user_func_array;
use function Symfony\Component\String\u;
use function Symfony\Component\Translation\t;

/**
 * Class AbstractGatewayAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Shipment
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class GatewayAction extends AbstractAction implements AdminActionInterface, RoutingActionInterface
{
    use FlashTrait;
    use HelperTrait;

    private GatewayRegistryInterface $gatewayRegistry;
    private PersisterInterface       $shipmentPersister;
    private LabelRenderer            $labelRenderer;
    private bool                     $debug;

    public function __construct(
        GatewayRegistryInterface $gatewayRegistry,
        PersisterInterface       $shipmentPersister,
        LabelRenderer            $labelRenderer,
        bool                     $debug
    ) {
        $this->gatewayRegistry = $gatewayRegistry;
        $this->shipmentPersister = $shipmentPersister;
        $this->labelRenderer = $labelRenderer;
        $this->debug = $debug;
    }

    public function __invoke(): Response
    {
        if ($this->request->isXmlHttpRequest()) {
            return new Response('XmlHttpRequest is not supported.', Response::HTTP_NOT_FOUND);
        }

        $action = $this->request->attributes->get('action');

        $shipment = $this->context->getResource();
        if (!$shipment instanceof ShipmentInterface) {
            throw new UnexpectedTypeException($shipment, ShipmentInterface::class);
        }

        $gateway = $this
            ->gatewayRegistry
            ->getGateway($shipment->getGatewayName());

        $method = u($action)->camel()->toString();
        $arguments = [$shipment];

        if ($action === GatewayActions::PRINT_LABEL) {
            $arguments[] = $this->request->query->get('types');
        }

        $redirect = $this->redirectToReferer($this->generateResourcePath($shipment->getSale()));

        try {
            $result = call_user_func_array([$gateway, $method], $arguments);

            if ($result) {
                $this->shipmentPersister->flush();
            }
        } catch (ShipmentGatewayException $e) {
            if ($this->debug) {
                throw $e;
            }

            $this->addFlash($e->getMessage(), 'danger');

            return $redirect;
        }

        if ($action === GatewayActions::PRINT_LABEL && !empty($result)) {
            try {
                return $this
                    ->labelRenderer
                    ->render($result);
            } catch (PdfException $e) {
                $this->addFlash(t('document.message.failed_to_generate', [], 'EkynaCommerce'), 'danger');
            }
        }

        return $redirect;
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_shipment_gateway',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_gateway',
                'path'     => '/gateway/{action}',
                'resource' => true,
                'methods'  => ['GET', 'POST'],
            ],
        ];
    }

    public static function buildRoute(Route $route, array $options): void
    {
        $route->addRequirements([
            'action' => 'ship|cancel|complete|print_label',
        ]);
    }
}
