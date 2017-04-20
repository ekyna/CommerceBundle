<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\SupplierOrder;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\CommerceBundle\Service\Document\RendererFactory;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\RoutingActionInterface;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Exception\PdfException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;

use function Symfony\Component\Translation\t;

/**
 * Class RenderAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\SupplierOrder
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
        $resource = $this->context->getResource();

        if (!$resource instanceof SupplierOrderInterface) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $renderer = $this
            ->rendererFactory
            ->createRenderer($resource);

        try {
            return $renderer->respond($this->request);
        } catch (PdfException $e) {
            $this->addFlash(t('document.message.failed_to_generate', [], 'EkynaCommerce'), 'danger');

            return $this->redirectToReferer($this->generateResourcePath($resource));
        }
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_supplier_order_render',
            'permission' => Permission::READ,
            'route'      => [
                'name'     => 'admin_%s_render',
                'path'     => '/render.{_format}',
                'resource' => true,
                'methods'  => ['GET'],
            ],
            'button'     => [
                'label'        => 'supplier_order.button.render',
                'trans_domain' => 'EkynaCommerce',
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
                '_format' => 'html|pdf|jpg',
            ]);
    }
}
