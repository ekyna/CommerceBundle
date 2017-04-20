<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Shipment;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\CommerceBundle\Form\Type\Shipment\GatewayDataType;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\FactoryTrait;
use Ekyna\Bundle\ResourceBundle\Action\FormTrait;
use Ekyna\Bundle\ResourceBundle\Action\RepositoryTrait;
use Ekyna\Bundle\ResourceBundle\Action\RoutingActionInterface;
use Ekyna\Bundle\ResourceBundle\Action\TemplatingTrait;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;

/**
 * Class GatewayFormAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Shipment
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class GatewayFormAction extends AbstractAction implements AdminActionInterface, RoutingActionInterface
{
    use FormTrait;
    use RepositoryTrait;
    use FactoryTrait;
    use TemplatingTrait;

    public function __invoke(): Response
    {
        $method = $this
            ->getRepository(ShipmentMethodInterface::class)
            ->find($this->request->attributes->getInt('shipmentMethodId'));

        if (!$method) {
            return new Response('Shipment method not found.', Response::HTTP_NOT_FOUND);
        }

        if (0 < $shipmentId = $this->request->query->getInt('shipmentId')) {
            $shipment = $this
                ->getRepository()
                ->find($shipmentId);
            if (!$shipment) {
                return new Response('Shipment not found.', Response::HTTP_NOT_FOUND);
            }
        } elseif (null === $isReturn = $this->request->query->get('return')) {
            return new Response("Expected 'shipmentId' or 'return' parameter.", Response::HTTP_NOT_FOUND);
        } else {
            /** @var ShipmentInterface $shipment */
            $shipment = $this->createResource();
            $shipment->setReturn((bool) $isReturn);
            if ($shipment->isReturn()) {
                $shipment->setAutoInvoice(false);
            }
        }

        $shipment->setMethod($method);

        $form = $this
            ->getFormFactory()
            ->createNamed('order_shipment', FormType::class, $shipment)
            ->add('gatewayData', GatewayDataType::class);

        $response = $this->render($this->options['template'], [
            'form' => $form->createView(),
        ]);

        $response->headers->add(['Content-Type' => 'application/xml']);

        return $response;
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_shipment_gateway_form',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_gateway_form',
                'path'     => '/gateway-form/{shipmentMethodId}',
                'methods'  => ['GET'],
            ],
            'options'    => [
                'template' => '@EkynaCommerce/Admin/Common/Shipment/gateway_form.xml.twig',
                'expose'   => true,
            ],
        ];
    }

    public static function buildRoute(Route $route, array $options): void
    {
        $route->addRequirements(['shipmentMethodId' => '\d+']);
    }
}
