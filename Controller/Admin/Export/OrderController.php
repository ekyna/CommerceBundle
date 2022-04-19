<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin\Export;

use DateTime;
use Ekyna\Bundle\CommerceBundle\Service\Order\OrderListExporter;
use Ekyna\Bundle\UiBundle\Service\FlashHelper;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Resource\Helper\File\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function sprintf;

/**
 * Class OrderController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin\Export
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderController
{
    private OrderListExporter     $orderListExporter;
    private UrlGeneratorInterface $urlGenerator;
    private FlashHelper           $flashHelper;
    private bool                  $debug;

    public function __construct(
        OrderListExporter     $orderListExporter,
        UrlGeneratorInterface $urlGenerator,
        FlashHelper           $flashHelper,
        bool                  $debug
    ) {
        $this->orderListExporter = $orderListExporter;
        $this->urlGenerator = $urlGenerator;
        $this->flashHelper = $flashHelper;
        $this->debug = $debug;
    }

    /**
     * Remaining orders export.
     */
    public function remainingOrders(): Response
    {
        try {
            $path = $this
                ->orderListExporter
                ->exportRemainingOrders();
        } catch (CommerceExceptionInterface $e) {
            if ($this->debug) {
                throw $e;
            }

            $this->flashHelper->addFlash($e->getMessage(), 'danger');

            return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        }

        $filename = sprintf('remaining-orders-%s.csv', (new DateTime())->format('Y-m-d'));

        return File::buildResponse($path, [
            'file_name' => $filename,
        ]);
    }

    /**
     * Due orders export.
     */
    public function dueOrders(): Response
    {
        try {
            $path = $this
                ->orderListExporter
                ->exportDueOrders();
        } catch (CommerceExceptionInterface $e) {
            if ($this->debug) {
                throw $e;
            }

            $this->flashHelper->addFlash($e->getMessage(), 'danger');

            return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        }

        $filename = sprintf('due-orders-%s.csv', (new DateTime())->format('Y-m-d'));

        return File::buildResponse($path, [
            'file_name' => $filename,
        ]);
    }

    /**
     * All due orders export.
     */
    public function allDueOrders(): Response
    {
        try {
            $path = $this
                ->orderListExporter
                ->exportAllDueOrders();
        } catch (CommerceExceptionInterface $e) {
            if ($this->debug) {
                throw $e;
            }

            $this->flashHelper->addFlash($e->getMessage(), 'danger');

            return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        }

        $filename = sprintf('due-orders-%s.zip', (new DateTime())->format('Y-m-d'));

        return File::buildResponse($path, [
            'file_name' => $filename,
        ]);
    }

    /**
     * Regular due orders export.
     */
    public function regularDueOrders(): Response
    {
        try {
            $path = $this
                ->orderListExporter
                ->exportRegularDueOrders();
        } catch (CommerceExceptionInterface $e) {
            if ($this->debug) {
                throw $e;
            }

            $this->flashHelper->addFlash($e->getMessage(), 'danger');

            return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        }

        $filename = sprintf('regular-due-orders-%s.csv', (new DateTime())->format('Y-m-d'));

        return File::buildResponse($path, [
            'file_name' => $filename,
        ]);
    }

    /**
     * Outstanding expired due orders export.
     */
    public function outstandingExpiredDueOrders(): Response
    {
        try {
            $path = $this
                ->orderListExporter
                ->exportOutstandingExpiredDueOrders();
        } catch (CommerceExceptionInterface $e) {
            if ($this->debug) {
                throw $e;
            }

            $this->flashHelper->addFlash($e->getMessage(), 'danger');

            return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        }

        $filename = sprintf('outstanding-expired-due-orders-%s.csv', (new DateTime())->format('Y-m-d'));

        return File::buildResponse($path, [
            'file_name' => $filename,
        ]);
    }

    /**
     * Outstanding fall due orders export.
     */
    public function outstandingFallDueOrders(): Response
    {
        try {
            $path = $this
                ->orderListExporter
                ->exportOutstandingFallDueOrders();
        } catch (CommerceExceptionInterface $e) {
            if ($this->debug) {
                throw $e;
            }

            $this->flashHelper->addFlash($e->getMessage(), 'danger');

            return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        }

        $filename = sprintf('outstanding-fall-due-orders-%s.csv', (new DateTime())->format('Y-m-d'));

        return File::buildResponse($path, [
            'file_name' => $filename,
        ]);
    }

    /**
     * Outstanding pending due orders export.
     */
    public function outstandingPendingDueOrders(): Response
    {
        try {
            $path = $this
                ->orderListExporter
                ->exportOutstandingPendingDueOrders();
        } catch (CommerceExceptionInterface $e) {
            if ($this->debug) {
                throw $e;
            }

            $this->flashHelper->addFlash($e->getMessage(), 'danger');

            return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        }

        $filename = sprintf('outstanding-pending-due-orders-%s.csv', (new DateTime())->format('Y-m-d'));

        return File::buildResponse($path, [
            'file_name' => $filename,
        ]);
    }
}
