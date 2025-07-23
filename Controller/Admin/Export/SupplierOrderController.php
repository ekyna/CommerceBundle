<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin\Export;

use Ekyna\Bundle\CommerceBundle\Service\Supplier\SupplierOrderExporter;
use Ekyna\Bundle\UiBundle\Service\FlashHelper;
use Ekyna\Component\Commerce\Common\Util\DateUtil;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function sprintf;

/**
 * Class SupplierOrderController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin\Export
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderController
{
    private SupplierOrderExporter $supplierOrderExporter;
    private UrlGeneratorInterface $urlGenerator;
    private FlashHelper           $flashHelper;
    private bool                  $debug;

    public function __construct(
        SupplierOrderExporter $supplierOrderExporter,
        UrlGeneratorInterface $urlGenerator,
        FlashHelper           $flashHelper,
        bool                  $debug
    ) {
        $this->supplierOrderExporter = $supplierOrderExporter;
        $this->urlGenerator = $urlGenerator;
        $this->flashHelper = $flashHelper;
        $this->debug = $debug;
    }

    /**
     * Suppliers expired due orders export.
     */
    public function suppliersExpiredDueOrders(): Response
    {
        try {
            $file = $this
                ->supplierOrderExporter
                ->exportSuppliersExpiredDueOrders();
        } catch (CommerceExceptionInterface $e) {
            if ($this->debug) {
                throw $e;
            }

            $this->flashHelper->addFlash($e->getMessage(), 'danger');

            return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        }

        $filename = sprintf('suppliers-expired-due-orders-%s.xls', DateUtil::today());

        return $file->download([
            'file_name' => $filename,
        ]);
    }

    /**
     * Suppliers fall due orders export.
     */
    public function suppliersFallDueOrders(): Response
    {
        try {
            $file = $this
                ->supplierOrderExporter
                ->exportSuppliersFallDueOrders();
        } catch (CommerceExceptionInterface $e) {
            if ($this->debug) {
                throw $e;
            }

            $this->flashHelper->addFlash($e->getMessage(), 'danger');

            return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        }

        $filename = sprintf('suppliers-fall-due-orders-%s.xls', DateUtil::today());

        return $file->download([
            'file_name' => $filename,
        ]);
    }

    /**
     * Forwarders expired due orders export.
     */
    public function forwardersExpiredDueOrders(): Response
    {
        try {
            $file = $this
                ->supplierOrderExporter
                ->exportForwardersExpiredDueOrders();
        } catch (CommerceExceptionInterface $e) {
            if ($this->debug) {
                throw $e;
            }

            $this->flashHelper->addFlash($e->getMessage(), 'danger');

            return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        }

        $filename = sprintf('forwarders-expired-due-orders-%s.xls', DateUtil::today());

        return $file->download([
            'file_name' => $filename,
        ]);
    }

    /**
     * Forwarders fall due orders export.
     */
    public function forwardersFallDueOrders(): Response
    {
        try {
            $file = $this
                ->supplierOrderExporter
                ->exportForwardersFallDueOrders();
        } catch (CommerceExceptionInterface $e) {
            if ($this->debug) {
                throw $e;
            }

            $this->flashHelper->addFlash($e->getMessage(), 'danger');

            return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        }

        $filename = sprintf('forwarders-fall-due-orders-%s.xls', DateUtil::today());

        return $file->download([
            'file_name' => $filename,
        ]);
    }
}
