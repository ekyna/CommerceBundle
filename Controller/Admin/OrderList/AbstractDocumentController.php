<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin\OrderList;

use DateTime;
use Ekyna\Bundle\CommerceBundle\Service\Document\RendererFactory;
use Ekyna\Bundle\CommerceBundle\Service\Document\RendererInterface;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Bundle\UiBundle\Service\FlashHelper;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Exception\PdfException;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use setasign\Fpdi\Fpdi;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Throwable;
use Tomsgu\PdfMerger\PdfCollection;

use Tomsgu\PdfMerger\PdfMerger;

use function array_map;
use function md5;
use function reset;
use function Symfony\Component\Translation\t;
use function sys_get_temp_dir;
use function uniqid;

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

        $resources = [];
        foreach ($ids as $id) {
            if (null !== $resource = $repository->find($id)) {
                $resources[] = $resource;
            }
        }

        $name = $configuration->getName();

        if (empty($resources)) {
            return new RedirectResponse(
                $this->urlGenerator->generate('admin_ekyna_commerce_list_' . $name)
            );
        }

        $type = $request->attributes->has('type') ? $request->attributes->get('type') : null;

        try {
            if (1 === count($resources)) {
                return $this
                    ->rendererFactory
                    ->createRenderer(reset($resources), $type)
                    ->respond($request);
            }

            return $this->renderMultiple($request, $resources, $name, $type);
        } catch (Throwable $throwable) {
            $this->flashHelper->addFlash(t('document.message.failed_to_generate', [], 'EkynaCommerce'), 'danger');

            return new RedirectResponse(
                $this->urlGenerator->generate('admin_ekyna_commerce_list_' . $name)
            );
        }
    }

    private function renderMultiple(Request $request, array $resources, string $name, ?string $type): Response
    {
        // TODO What if using HTML format ?
        $format = RendererInterface::FORMAT_PDF;

        $pdfCollection = new PdfCollection();

        foreach ($resources as $resource) {
            $renderer = $this
                ->rendererFactory
                ->createRenderer($resource, $type);

            $pdfCollection->addPdf($renderer->create($format));
        }

        $fpdi = new Fpdi();
        $merger = new PdfMerger($fpdi);

        $path = sys_get_temp_dir() . '/' . uniqid() . '.' . $format;

        $merger->merge($pdfCollection, $path, PdfMerger::MODE_FILE);

        $response = new BinaryFileResponse($path);
        $response
            ->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $name . '.pdf'
            );

        return $response;
    }
}
