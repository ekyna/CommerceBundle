<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin\OrderList;

use Ekyna\Bundle\CommerceBundle\Service\Document\RendererFactory;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Bundle\UiBundle\Service\FlashHelper;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Exception\PdfException;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function array_map;
use function Symfony\Component\Translation\t;

/**
 * Class AbstractDocumentController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin\OrderList
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractDocumentController
{
    protected static string $resource;

    private ResourceHelper             $resourceHelper;
    private RepositoryFactoryInterface $repositoryFactory;
    private RendererFactory            $rendererFactory;
    private FlashHelper                $flashHelper;
    private UrlGeneratorInterface      $urlGenerator;

    public function __construct(
        ResourceHelper             $resourceHelper,
        RepositoryFactoryInterface $repositoryFactory,
        RendererFactory            $rendererFactory,
        FlashHelper                $flashHelper,
        UrlGeneratorInterface      $urlGenerator
    ) {
        $this->resourceHelper = $resourceHelper;
        $this->repositoryFactory = $repositoryFactory;
        $this->rendererFactory = $rendererFactory;
        $this->flashHelper = $flashHelper;
        $this->urlGenerator = $urlGenerator;
    }

    public function __invoke(Request $request): Response
    {
        $this->resourceHelper->isGranted(Permission::READ, static::$resource);

        $configuration = $this->resourceHelper->getResourceConfig(static::$resource);

        $repository = $this->repositoryFactory->getRepository(static::$resource);

        $ids = array_map(fn($v) => (int)$v, (array)$request->query->get('id'));

        $invoices = [];
        foreach ($ids as $id) {
            if (null !== $invoice = $repository->find($id)) {
                $invoices[] = $invoice;
            }
        }

        if (empty($invoices)) {
            return new RedirectResponse(
                $this->urlGenerator->generate('admin_ekyna_commerce_list_' . $configuration->getName())
            );
        }

        $renderer = $this
            ->rendererFactory
            ->createRenderer($invoices);

        try {
            return $renderer->respond($request);
        } catch (PdfException $e) {
            $this->flashHelper->addFlash(t('document.message.failed_to_generate', [], 'EkynaCommerce'), 'danger');

            return new RedirectResponse(
                $this->urlGenerator->generate('admin_ekyna_commerce_list_' . $configuration->getName())
            );
        }
    }
}
