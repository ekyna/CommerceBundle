<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Sale;

use Ekyna\Bundle\CommerceBundle\Service\Document\RendererFactory;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\RoutingActionInterface;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Component\Commerce\Document\Builder\DocumentBuilderInterface;
use Ekyna\Component\Commerce\Document\Model\Document;
use Ekyna\Component\Commerce\Document\Util\DocumentUtil;
use Ekyna\Component\Commerce\Document\Calculator\DocumentCalculatorInterface;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Exception\PdfException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;

use function in_array;
use function Symfony\Component\Translation\t;

/**
 * Class DocumentRenderAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Sale
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DocumentRenderAction extends AbstractSaleAction implements RoutingActionInterface
{
    use HelperTrait;
    use FlashTrait;

    public function __construct(
        private readonly DocumentBuilderInterface    $documentBuilder,
        private readonly DocumentCalculatorInterface $documentCalculator,
        private readonly RendererFactory             $rendererFactory
    ) {
    }

    public function __invoke(): Response
    {
        if (!$sale = $this->getSale()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $type = $this->request->attributes->get('type');
        $available = DocumentUtil::getSaleEditableDocumentTypes($sale, false);
        if (!in_array($type, $available, true)) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $document = new Document();
        $document
            ->setSale($sale)
            ->setType($type)
            ->setLocale($this->request->query->get('locale'))
            ->setCurrency($this->request->query->get('currency'));

        $this->documentBuilder->build($document);
        $this->documentCalculator->calculate($document);

        $renderer = $this->rendererFactory->createRenderer($document);

        try {
            return $renderer->respond($this->request);
        } catch (PdfException $e) {
            $this->addFlash(t('document.message.failed_to_generate', [], 'EkynaCommerce'), 'danger');

            return $this->redirectToReferer($this->generateResourcePath($sale));
        }
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_sale_document_render',
            'permission' => Permission::READ,
            'route'      => [
                'name'     => 'admin_%s_document_render',
                'path'     => '/document/render/{type}.{_format}',
                'resource' => true,
                'methods'  => ['GET'],
            ],
            'button'     => [
                'label'        => 'button.render',
                'trans_domain' => 'EkynaUi',
                'theme'        => 'default',
                'icon'         => 'print',
            ],
        ];
    }

    public static function buildRoute(Route $route, array $options): void
    {
        $route->addRequirements([
            'type'    => '[a-z]+',
            '_format' => 'html|pdf|jpg',
        ]);
    }
}
