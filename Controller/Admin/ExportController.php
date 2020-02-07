<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\CommerceBundle\Form\Type\Accounting\ExportType;
use Ekyna\Bundle\CommerceBundle\Service\Order\OrderListExporter;
use Ekyna\Bundle\CommerceBundle\Service\Stat\StatExporter;
use Ekyna\Bundle\CoreBundle\Controller\Controller;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Stream;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Class ExportController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ExportController extends Controller
{
    /**
     * Accounting export action
     *
     * @param Request $request
     *
     * @return Response
     */
    public function accountingAction(Request $request): Response
    {
        $form = $this->createForm(ExportType::class);

        $form->handleRequest($request);

        if (!($form->isSubmitted() && $form->isValid())) {
            return $this->redirectToReferer($this->generateUrl('ekyna_admin_dashboard'));
        }

        $year = $form->get('year')->getData();
        $month = $form->get('month')->getData();

        // TODO if month is null, schedule background task
        if (is_null($month)) {

        }

        try {
            $path = $this
                ->get('ekyna_commerce.accounting.exporter')
                ->export($year, $month);
        } catch (CommerceExceptionInterface $e) {
            if ($this->getParameter('kernel.debug')) {
                throw $e;
            }

            $this->addFlash($e->getMessage(), 'danger');

            return $this->doRedirect();
        }

        $filename = sprintf('accounting_%s.zip', $year . ($month ? '-' : '') . $month);

        return $this->doRespond($path, $filename);
    }

    /**
     * Due invoices export.
     *
     * @return Response
     */
    public function dueInvoicesAction(): Response
    {
        try {
            $path = $this
                ->get('ekyna_commerce.order_invoice.exporter')
                ->exportDueInvoices();
        } catch (CommerceExceptionInterface $e) {
            if ($this->getParameter('kernel.debug')) {
                throw $e;
            }

            $this->addFlash($e->getMessage(), 'danger');

            return $this->doRedirect();
        }

        $filename = sprintf('due-invoices-%s.csv', (new \DateTime())->format('Y-m-d'));

        return $this->doRespond($path, $filename);
    }

    /**
     * Fall invoices export.
     *
     * @return Response
     */
    public function fallInvoicesAction(): Response
    {
        try {
            $path = $this
                ->get('ekyna_commerce.order_invoice.exporter')
                ->exportFallInvoices();
        } catch (CommerceExceptionInterface $e) {
            if ($this->getParameter('kernel.debug')) {
                throw $e;
            }

            $this->addFlash($e->getMessage(), 'danger');

            return $this->doRedirect();
        }

        $filename = sprintf('fall-invoices-%s.csv', (new \DateTime())->format('Y-m-d'));

        return $this->doRespond($path, $filename);
    }

    /**
     * Remaining orders export.
     *
     * @return Response
     */
    public function remainingOrdersAction(): Response
    {
        try {
            $path = $this
                ->get(OrderListExporter::class)
                ->exportRemainingOrders();
        } catch (CommerceExceptionInterface $e) {
            if ($this->getParameter('kernel.debug')) {
                throw $e;
            }

            $this->addFlash($e->getMessage(), 'danger');

            return $this->doRedirect();
        }

        $filename = sprintf('remaining-orders-%s.csv', (new \DateTime())->format('Y-m-d'));

        return $this->doRespond($path, $filename);
    }

    /**
     * Due orders export.
     *
     * @return Response
     */
    public function dueOrdersAction(): Response
    {
        try {
            $path = $this
                ->get(OrderListExporter::class)
                ->exportDueOrders();
        } catch (CommerceExceptionInterface $e) {
            if ($this->getParameter('kernel.debug')) {
                throw $e;
            }

            $this->addFlash($e->getMessage(), 'danger');

            return $this->doRedirect();
        }

        $filename = sprintf('due-orders-%s.csv', (new \DateTime())->format('Y-m-d'));

        return $this->doRespond($path, $filename);
    }

    /**
     * All due orders export.
     *
     * @return Response
     */
    public function allDueOrdersAction(): Response
    {
        try {
            $path = $this
                ->get(OrderListExporter::class)
                ->exportAllDueOrders();
        } catch (CommerceExceptionInterface $e) {
            if ($this->getParameter('kernel.debug')) {
                throw $e;
            }

            $this->addFlash($e->getMessage(), 'danger');

            return $this->doRedirect();
        }

        $filename = sprintf('due-orders-%s.zip', (new \DateTime())->format('Y-m-d'));

        return $this->doRespond($path, $filename);
    }

    /**
     * Regular due orders export.
     *
     * @return Response
     */
    public function regularDueOrdersAction(): Response
    {
        try {
            $path = $this
                ->get(OrderListExporter::class)
                ->exportRegularDueOrders();
        } catch (CommerceExceptionInterface $e) {
            if ($this->getParameter('kernel.debug')) {
                throw $e;
            }

            $this->addFlash($e->getMessage(), 'danger');

            return $this->doRedirect();
        }

        $filename = sprintf('regular-due-orders-%s.csv', (new \DateTime())->format('Y-m-d'));

        return $this->doRespond($path, $filename);
    }

    /**
     * Outstanding expired due orders export.
     *
     * @return Response
     */
    public function outstandingExpiredDueOrdersAction(): Response
    {
        try {
            $path = $this
                ->get(OrderListExporter::class)
                ->exportOutstandingExpiredDueOrders();
        } catch (CommerceExceptionInterface $e) {
            if ($this->getParameter('kernel.debug')) {
                throw $e;
            }

            $this->addFlash($e->getMessage(), 'danger');

            return $this->doRedirect();
        }

        $filename = sprintf('outstanding-expired-due-orders-%s.csv', (new \DateTime())->format('Y-m-d'));

        return $this->doRespond($path, $filename);
    }

    /**
     * Outstanding fall due orders export.
     *
     * @return Response
     */
    public function outstandingFallDueOrdersAction(): Response
    {
        try {
            $path = $this
                ->get(OrderListExporter::class)
                ->exportOutstandingFallDueOrders();
        } catch (CommerceExceptionInterface $e) {
            if ($this->getParameter('kernel.debug')) {
                throw $e;
            }

            $this->addFlash($e->getMessage(), 'danger');

            return $this->doRedirect();
        }

        $filename = sprintf('outstanding-fall-due-orders-%s.csv', (new \DateTime())->format('Y-m-d'));

        return $this->doRespond($path, $filename);
    }

    /**
     * Outstanding pending due orders export.
     *
     * @return Response
     */
    public function outstandingPendingDueOrdersAction(): Response
    {
        try {
            $path = $this
                ->get(OrderListExporter::class)
                ->exportOutstandingPendingDueOrders();
        } catch (CommerceExceptionInterface $e) {
            if ($this->getParameter('kernel.debug')) {
                throw $e;
            }

            $this->addFlash($e->getMessage(), 'danger');

            return $this->doRedirect();
        }

        $filename = sprintf('outstanding-pending-due-orders-%s.csv', (new \DateTime())->format('Y-m-d'));

        return $this->doRespond($path, $filename);
    }

    /**
     * Suppliers expired due orders export.
     *
     * @return Response
     */
    public function suppliersExpiredDueOrdersAction(): Response
    {
        try {
            $path = $this
                ->get('ekyna_commerce.supplier_order.exporter')
                ->exportSuppliersExpiredDueOrders();
        } catch (CommerceExceptionInterface $e) {
            if ($this->getParameter('kernel.debug')) {
                throw $e;
            }

            $this->addFlash($e->getMessage(), 'danger');

            return $this->doRedirect();
        }

        $filename = sprintf('suppliers-expired-due-orders-%s.csv', (new \DateTime())->format('Y-m-d'));

        return $this->doRespond($path, $filename);
    }

    /**
     * Suppliers fall due orders export.
     *
     * @return Response
     */
    public function suppliersFallDueOrdersAction(): Response
    {
        try {
            $path = $this
                ->get('ekyna_commerce.supplier_order.exporter')
                ->exportSuppliersFallDueOrders();
        } catch (CommerceExceptionInterface $e) {
            if ($this->getParameter('kernel.debug')) {
                throw $e;
            }

            $this->addFlash($e->getMessage(), 'danger');

            return $this->doRedirect();
        }

        $filename = sprintf('suppliers-fall-due-orders-%s.csv', (new \DateTime())->format('Y-m-d'));

        return $this->doRespond($path, $filename);
    }

    /**
     * Forwarders expired due orders export.
     *
     * @return Response
     */
    public function forwardersExpiredDueOrdersAction(): Response
    {
        try {
            $path = $this
                ->get('ekyna_commerce.supplier_order.exporter')
                ->exportForwardersExpiredDueOrders();
        } catch (CommerceExceptionInterface $e) {
            if ($this->getParameter('kernel.debug')) {
                throw $e;
            }

            $this->addFlash($e->getMessage(), 'danger');

            return $this->doRedirect();
        }

        $filename = sprintf('forwarders-expired-due-orders-%s.csv', (new \DateTime())->format('Y-m-d'));

        return $this->doRespond($path, $filename);
    }

    /**
     * Forwarders fall due orders export.
     *
     * @return Response
     */
    public function forwardersFallDueOrdersAction(): Response
    {
        try {
            $path = $this
                ->get('ekyna_commerce.supplier_order.exporter')
                ->exportForwardersFallDueOrders();
        } catch (CommerceExceptionInterface $e) {
            if ($this->getParameter('kernel.debug')) {
                throw $e;
            }

            $this->addFlash($e->getMessage(), 'danger');

            return $this->doRedirect();
        }

        $filename = sprintf('forwarders-fall-due-orders-%s.csv', (new \DateTime())->format('Y-m-d'));

        return $this->doRespond($path, $filename);
    }

    /**
     * Regions order statistics export.
     *
     * @return Response
     */
    public function regionsOrdersStatsAction(): Response
    {
        $from = new \DateTime('2019-01-01');
        $to = new \DateTime('2019-12-31');

        try {
            $path = $this
                ->get(StatExporter::class)
                ->exportByMonths($from, $to);
        } catch (CommerceExceptionInterface $e) {
            if ($this->getParameter('kernel.debug')) {
                throw $e;
            }

            $this->addFlash($e->getMessage(), 'danger');

            return $this->doRedirect();
        }

        $filename = sprintf('orders-stats_%s_%s.csv', $from->format('Y-m'), $to->format('Y-m'));

        return $this->doRespond($path, $filename);
    }

    /**
     * Regions invoices stats export.
     *
     * @return Response
     */
    public function regionsInvoicesStatsAction(): Response
    {
        $from = new \DateTime('2019-01-01');
        $to = new \DateTime('2019-12-31');

        try {
            $path = $this
                ->get('ekyna_commerce.order_invoice.exporter')
                ->exportRegionsInvoices($from, $to);
        } catch (CommerceExceptionInterface $e) {
            if ($this->getParameter('kernel.debug')) {
                throw $e;
            }

            $this->addFlash($e->getMessage(), 'danger');

            return $this->doRedirect();
        }

        $filename = sprintf('invoices-stats_%s_%s.csv', $from->format('Y-m'), $to->format('Y-m'));

        return $this->doRespond($path, $filename);
    }

    /**
     * Builds and returns the file response.
     *
     * @param string $path
     * @param string $filename
     *
     * @return Response
     */
    protected function doRespond($path, $filename): Response
    {
        clearstatcache(true, $path);

        $stream = new Stream($path);
        $response = new BinaryFileResponse($stream);
        $response->headers->set('Content-Type', 'text/csv');
        $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    /**
     * Builds and return the redirection.
     *
     * @return Response
     */
    protected function doRedirect(): Response
    {
        return $this->redirectToReferer($this->generateUrl('ekyna_admin_dashboard'));
    }
}
