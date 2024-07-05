<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin\Export;

use Ekyna\Bundle\CommerceBundle\Service\Order\OrderInvoiceExporter;
use Ekyna\Bundle\UiBundle\Service\FlashHelper;
use Ekyna\Component\Commerce\Common\Util\DateUtil;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Resource\Helper\File\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function sprintf;

/**
 * Class InvoiceController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin\Export
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InvoiceController
{
    public function __construct(
        private readonly OrderInvoiceExporter  $orderInvoiceExporter,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly FlashHelper           $flashHelper,
        private readonly bool                  $debug
    ) {
    }

    /**
     * Due invoices export.
     */
    public function dueInvoices(): Response
    {
        try {
            $path = $this
                ->orderInvoiceExporter
                ->exportDueInvoices();
        } catch (CommerceExceptionInterface $e) {
            if ($this->debug) {
                throw $e;
            }

            $this->flashHelper->addFlash($e->getMessage(), 'danger');

            return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        }

        $filename = sprintf('due-invoices-%s.csv', DateUtil::today());

        return File::buildResponse($path, [
            'file_name' => $filename,
        ]);
    }

    /**
     * Fall invoices export.
     */
    public function fallInvoices(): Response
    {
        try {
            $path = $this
                ->orderInvoiceExporter
                ->exportFallInvoices();
        } catch (CommerceExceptionInterface $e) {
            if ($this->debug) {
                throw $e;
            }

            $this->flashHelper->addFlash($e->getMessage(), 'danger');

            return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        }

        $filename = sprintf('fall-invoices-%s.csv', DateUtil::today());

        return File::buildResponse($path, [
            'file_name' => $filename,
        ]);
    }
}
