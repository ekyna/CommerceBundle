<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\CoreBundle\Controller\Controller;
use Ekyna\Component\Commerce\Exception;
use Symfony\Component\HttpFoundation\Request;

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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function invoice(Request $request)
    {
        $this->isGranted('VIEW', 'ekyna_commerce_order');

        $configuration = $this->get('ekyna_commerce.order_invoice.configuration');

        $type = $configuration->getTableType();

        $table = $this->get('table.factory')->createTable('invoices', $type);

        if (null !== $response = $table->handleRequest($request)) {
            return $response;
        }

        $this->container
            ->get('ekyna_admin.menu.builder')
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function invoiceDocument(Request $request)
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
            return $this->redirectToRoute('ekyna_commerce_admin_order_list_invoice');
        }

        $renderer = $this
            ->get('ekyna_commerce.document.renderer_factory')
            ->createRenderer($invoices);

        return $renderer->respond($request);
    }

    /**
     * Order payments list action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function payment(Request $request)
    {
        $this->isGranted('VIEW', 'ekyna_commerce_order_payment');

        $configuration = $this->get('ekyna_commerce.order_payment.configuration');

        $type = $configuration->getTableType();

        $table = $this->get('table.factory')->createTable('payments', $type);

        if (null !== $response = $table->handleRequest($request)) {
            return $response;
        }

        $this->container
            ->get('ekyna_admin.menu.builder')
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function shipment(Request $request)
    {
        $this->isGranted('VIEW', 'ekyna_commerce_order_shipment');

        $configuration = $this->get('ekyna_commerce.order_shipment.configuration');

        $type = $configuration->getTableType();

        $table = $this->get('table.factory')->createTable('shipments', $type);

        if (null !== $response = $table->handleRequest($request)) {
            return $response;
        }

        $this->container
            ->get('ekyna_admin.menu.builder')
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function shipmentDocument(Request $request)
    {
        $this->isGranted('VIEW', 'ekyna_commerce_order_shipment');

        $shipments = $this
            ->get('ekyna_commerce.order_shipment.repository')
            ->findBy(['id' => (array)$request->query->get('id')]);

        if (empty($shipments)) {
            return $this->redirectToRoute('ekyna_commerce_admin_order_list_shipment');
        }

        $renderer = $this
            ->get('ekyna_commerce.document.renderer_factory')
            ->createRenderer($shipments, $request->attributes->get('type'));

        return $renderer->respond($request);
    }

    /**
     * Platform action action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function shipmentPlatform(Request $request)
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
