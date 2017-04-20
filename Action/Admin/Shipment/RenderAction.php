<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Shipment;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\CommerceBundle\Service\Document\RendererFactory;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\RoutingActionInterface;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Exception\PdfException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;

use function Symfony\Component\Translation\t;

/**
 * Class RenderAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Shipment
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RenderAction extends AbstractAction implements AdminActionInterface, RoutingActionInterface
{
    use FlashTrait;
    use HelperTrait;

    private RendererFactory $rendererFactory;

    public function __construct(RendererFactory $rendererFactory)
    {
        $this->rendererFactory = $rendererFactory;
    }

    public function __invoke(): Response
    {
        $shipment = $this->context->getResource();

        if (!$shipment instanceof ShipmentInterface) {
            throw new UnexpectedTypeException($shipment, ShipmentInterface::class);
        }

        $type = $this->request->attributes->get('type');

        $renderer = $this
            ->rendererFactory
            ->createRenderer($shipment, $type);

        try {
            return $renderer->respond($this->request);
        } catch (PdfException $e) {
            $this->addFlash(t('document.message.failed_to_generate', [], 'EkynaCommerce'), 'danger');

            return $this->redirectToReferer($this->generateResourcePath($shipment->getSale()));
        }
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_shipment_render',
            'permission' => Permission::READ,
            'route'      => [
                'name'     => 'admin_%s_render',
                'path'     => '/render/{type}.{_format}',
                'resource' => true,
                'methods'  => ['GET'],
            ],
            'button'     => [
                'label'        => 'button.download',
                'trans_domain' => 'EkynaUi',
                'theme'        => 'primary',
                'icon'         => 'download',
            ],
        ];
    }

    public static function buildRoute(Route $route, array $options): void
    {
        $route
            ->addDefaults([
                '_format' => 'pdf',
            ])
            ->addRequirements([
                'type'    => 'shipment_form|shipment_bill',
                '_format' => 'html|pdf|jpg',
            ]);
    }
}
