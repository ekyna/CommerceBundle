<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin\Export;

use DateTime;
use Ekyna\Bundle\CommerceBundle\Service\Supplier\SupplierOrderItemExporter;
use Ekyna\Bundle\UiBundle\Service\FlashHelper;
use Ekyna\Component\Commerce\Common\Util\DateUtil;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Resource\Helper\File\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function sprintf;

/**
 * Class SupplierOrderItemController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin\Export
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderItemController
{
    private SupplierOrderItemExporter $exporter;
    private UrlGeneratorInterface     $urlGenerator;
    private FlashHelper               $flashHelper;
    private bool                      $debug;

    public function __construct(
        SupplierOrderItemExporter $exporter,
        UrlGeneratorInterface     $urlGenerator,
        FlashHelper               $flashHelper,
        bool                      $debug
    ) {
        $this->exporter = $exporter;
        $this->urlGenerator = $urlGenerator;
        $this->flashHelper = $flashHelper;
        $this->debug = $debug;
    }

    public function exportPaidButNotDelivered(): Response
    {
        try {
            $path = $this
                ->exporter
                ->exportPaidButNotDelivered();
        } catch (CommerceExceptionInterface $e) {
            if ($this->debug) {
                throw $e;
            }

            $this->flashHelper->addFlash($e->getMessage(), 'danger');

            return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        }

        $filename = sprintf(
            'paid-but-not-delivered-supplier-order-items-%s.csv',
            DateUtil::today()
        );

        return File::buildResponse($path, [
            'file_name' => $filename,
        ]);
    }
}
