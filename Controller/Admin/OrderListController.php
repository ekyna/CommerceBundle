<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Menu\MenuBuilder;
use Ekyna\Bundle\CommerceBundle\Service\Document\RendererFactory;
use Ekyna\Bundle\CoreBundle\Controller\Controller;
use Ekyna\Component\Commerce\Exception;
use Ekyna\Component\Commerce\Exception\PdfException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class OrderInvoiceController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderListController extends Controller
{
    /**
     * Order invoices list action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function invoice(Request $request): Response
    {
        $this->isGranted('VIEW', 'ekyna_commerce_order');

        $configuration = $this->get('ekyna_commerce.order_invoice.configuration');

        $type = $configuration->getTableType();

        $table = $this->get('table.factory')->createTable('invoices', $type);

        if (null !== $response = $table->handleRequest($request)) {
            return $response;
        }

        $this->container
            ->get(MenuBuilder::class)
            ->breadcrumbAppend(
                'ekyna_commerce.order_invoices_list',
                $configuration->getResourceLabel(true),
                'ekyna_commerce_admin_order_list_invoice'
            );

        return $this->render('@EkynaCommerce/Admin/OrderList/invoice.html.twig', [
            'invoices' => $table->createView(),
        ]);
    }

    /**
     * Order invoices documents action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function invoiceDocument(Request $request): Response
    {
        $this->isGranted('VIEW', 'ekyna_commerce_order_invoice');

        $ids = (array)$request->query->get('id');
        $repository = $this->get('ekyna_commerce.order_invoice.repository');

        $invoices = [];

        foreach ($ids as $id) {
            if (null !== $invoice = $repository->find($id)) {
                $invoices[] = $invoice;
            }
        }

        if (empty($invoices)) {
            return $this->redirectToReferer(
                $this->generateUrl('ekyna_commerce_admin_order_list_invoice')
            );
        }

        $renderer = $this
            ->get(RendererFactory::class)
            ->createRenderer($invoices);

        try {
            return $renderer->respond($request);
        } catch (PdfException $e) {
            $this->addFlash('ekyna_commerce.document.message.failed_to_generate', 'danger');

            return $this->redirectToReferer(
                $this->generateUrl('ekyna_commerce_admin_order_list_invoice')
            );
        }
    }

    /**
     * Order payments list action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function payment(Request $request): Response
    {
        $this->isGranted('VIEW', 'ekyna_commerce_order_payment');

        $configuration = $this->get('ekyna_commerce.order_payment.configuration');

        $type = $configuration->getTableType();

        $table = $this->get('table.factory')->createTable('payments', $type);

        if (null !== $response = $table->handleRequest($request)) {
            return $response;
        }

        $this->container
            ->get(MenuBuilder::class)
            ->breadcrumbAppend(
                'ekyna_commerce.order_payments_list',
                $configuration->getResourceLabel(true),
                'ekyna_commerce_admin_order_list_payment'
            );

        return $this->render('@EkynaCommerce/Admin/OrderList/payment.html.twig', [
            'payments' => $table->createView(),
        ]);
    }

    /**
     * Order shipments list action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function shipment(Request $request): Response
    {
        $this->isGranted('VIEW', 'ekyna_commerce_order_shipment');

        $configuration = $this->get('ekyna_commerce.order_shipment.configuration');

        $type = $configuration->getTableType();

        $table = $this->get('table.factory')->createTable('shipments', $type);

        if (null !== $response = $table->handleRequest($request)) {
            return $response;
        }

        $this->container
            ->get(MenuBuilder::class)
            ->breadcrumbAppend(
                'ekyna_commerce.order_shipments_list',
                $configuration->getResourceLabel(true),
                'ekyna_commerce_admin_order_list_shipment'
            );

        return $this->render('@EkynaCommerce/Admin/OrderList/shipment.html.twig', [
            'shipments' => $table->createView(),
        ]);
    }

    /**
     * Order shipments documents action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function shipmentDocument(Request $request): Response
    {
        $this->isGranted('VIEW', 'ekyna_commerce_order_shipment');

        $shipments = $this
            ->get('ekyna_commerce.order_shipment.repository')
            ->findBy(['id' => (array)$request->query->get('id')]);

        if (empty($shipments)) {
            return $this->redirectToReferer(
                $this->generateUrl('ekyna_commerce_admin_order_list_shipment')
            );
        }

        $renderer = $this
            ->get(RendererFactory::class)
            ->createRenderer($shipments, $request->attributes->get('type'));

        try {
            return $renderer->respond($request);
        } catch (PdfException $e) {
            $this->addFlash('ekyna_commerce.document.message.failed_to_generate', 'danger');

            return $this->redirectToReferer(
                $this->generateUrl('ekyna_commerce_admin_order_list_shipment')
            );
        }
    }

    /**
     * Platform action action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function shipmentPlatform(Request $request): Response
    {
        $platformName = $request->attributes->get('name');
        $actionName = $request->attributes->get('action');

        $shipments = []; // TODO

        try {
            $response = $this
                ->get('ekyna_commerce.shipment.gateway_registry')
                ->executePlatformAction($platformName, $actionName, $shipments);

            if (null !== $response) {
                return $response;
            }
        } catch (Exception\ShipmentGatewayException $e) {
            if ($this->getParameter('kernel.debug')) {
                throw $e;
            }

            $this->addFlash($e->getMessage(), 'danger');
        }

        if (!empty($referer = $request->headers->get('referer'))) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('ekyna_commerce_admin_order_list_shipment');
    }
}
