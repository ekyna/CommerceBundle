<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin\Export;

use DateTime;
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
    private OrderInvoiceExporter  $orderInvoiceExporter;
    private UrlGeneratorInterface $urlGenerator;
    private FlashHelper           $flashHelper;
    private bool                  $debug;

    public function __construct(
        OrderInvoiceExporter  $orderInvoiceExporter,
        UrlGeneratorInterface $urlGenerator,
        FlashHelper           $flashHelper,
        bool                  $debug
    ) {
        $this->orderInvoiceExporter = $orderInvoiceExporter;
        $this->urlGenerator = $urlGenerator;
        $this->flashHelper = $flashHelper;
        $this->debug = $debug;
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

    /**
     * Regions invoices stats export.
     *
     * @deprecated
     * @TODO Remove
     */
    public function regionsInvoicesStats(): Response
    {
        $from = new DateTime('2019-01-01');
        $to = new DateTime('2019-12-31');

        try {
            $path = $this
                ->orderInvoiceExporter
                ->exportRegionsInvoices($from, $to);
        } catch (CommerceExceptionInterface $e) {
            if ($this->debug) {
                throw $e;
            }

            $this->flashHelper->addFlash($e->getMessage(), 'danger');

            return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        }

        $filename = sprintf('invoices-stats_%s_%s.csv', $from->format('Y-m'), $to->format('Y-m'));

        return File::buildResponse($path, [
            'file_name' => $filename,
        ]);
    }
}
