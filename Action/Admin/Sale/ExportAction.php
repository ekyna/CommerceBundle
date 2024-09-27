<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Sale;

use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\RoutingActionInterface;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Component\Commerce\Common\Export\SaleCsvExporter;
use Ekyna\Component\Commerce\Common\Export\SaleXlsExporter;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Stream;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Route;

use function clearstatcache;
use function sprintf;

/**
 * Class ExportAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Sale
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ExportAction extends AbstractSaleAction implements RoutingActionInterface
{
    use HelperTrait;
    use FlashTrait;

    private SaleCsvExporter $csvExporter;
    private SaleXlsExporter $xlsExporter;
    private bool            $debug;

    public function __construct(SaleCsvExporter $csvExporter, SaleXlsExporter $xlsExporter, bool $debug)
    {
        $this->csvExporter = $csvExporter;
        $this->xlsExporter = $xlsExporter;
        $this->debug = $debug;
    }

    public function __invoke(): Response
    {
        if (!$sale = $this->getSale()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $format = $this->request->getRequestFormat('csv');
        if ($format === 'csv') {
            $exporter = $this->csvExporter;
        } elseif ($format === 'xls') {
            $exporter = $this->xlsExporter;
        } else {
            throw new InvalidArgumentException("Unexpected format '$format'");
        }

        $internal = $this->request->query->getBoolean('internal');

        try {
            $path = $exporter->export($sale, $internal);
        } catch (CommerceExceptionInterface $e) {
            if ($this->debug) {
                throw $e;
            }

            $this->addFlash($e->getMessage(), 'danger');

            return $this->redirect($this->generateResourcePath($sale));
        }

        // TODO Use $exporter->download() (same in account controller)
        clearstatcache(true, $path);

        $response = new BinaryFileResponse(new Stream($path));

        $fileName = sprintf('%s%s.%s',
            $sale->getNumber(),
            $internal ? '_internal' : '',
            $format
        );

        $disposition = $response
            ->headers
            ->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $fileName);

        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'text/csv');

        return $response;
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_sale_export',
            'permission' => Permission::READ,
            'route'      => [
                'name'     => 'admin_%s_export',
                'path'     => '/export.{_format}',
                'resource' => true,
                'methods'  => ['GET'],
            ],
        ];
    }

    public static function buildRoute(Route $route, array $options): void
    {
        $route->addRequirements([
            '_format' => 'csv|xls',
        ]);
    }
}
