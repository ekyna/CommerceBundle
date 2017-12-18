<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\CommerceBundle\Table\Type;
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
    public function invoiceAction(Request $request)
    {
        $this->isGranted('VIEW', $this->getParameter('ekyna_commerce.order_invoice.class'));

        $table = $this
            ->get('table.factory')
            ->createTable('invoices', Type\OrderInvoiceType::class);

        if (null !== $response = $table->handleRequest($request)) {
            return $response;
        }

        $this->container
            ->get('ekyna_admin.menu.builder')
            ->breadcrumbAppend(
                'ekyna_commerce.order_invoices_list',
                'ekyna_commerce.order_invoice.label.plural',
                'ekyna_commerce_admin_order_list_invoice'
            );

        return $this->render('EkynaCommerceBundle:Admin/OrderList:invoice.html.twig', [
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
    public function invoiceDocumentAction(Request $request)
    {
        $this->isGranted('VIEW', $this->getParameter('ekyna_commerce.order_invoice.class'));

        $ids = (array) $request->query->get('id');
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
    public function paymentAction(Request $request)
    {
        $this->isGranted('VIEW', $this->getParameter('ekyna_commerce.order_payment.class'));

        $table = $this
            ->get('table.factory')
            ->createTable('payments', Type\OrderPaymentType::class);

        if (null !== $response = $table->handleRequest($request)) {
            return $response;
        }

        $this->container
            ->get('ekyna_admin.menu.builder')
            ->breadcrumbAppend(
                'ekyna_commerce.order_payments_list',
                'ekyna_commerce.order_payment.label.plural',
                'ekyna_commerce_admin_order_list_payment'
            );

        return $this->render('EkynaCommerceBundle:Admin/OrderList:payment.html.twig', [
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
    public function shipmentAction(Request $request)
    {
        $this->isGranted('VIEW', $this->getParameter('ekyna_commerce.order_shipment.class'));

        $table = $this
            ->get('table.factory')
            ->createTable('shipments', Type\OrderShipmentType::class);

        if (null !== $response = $table->handleRequest($request)) {
            return $response;
        }

        $this->container
            ->get('ekyna_admin.menu.builder')
            ->breadcrumbAppend(
                'ekyna_commerce.order_shipments_list',
                'ekyna_commerce.order_shipment.label.plural',
                'ekyna_commerce_admin_order_list_shipment'
            );

        return $this->render('EkynaCommerceBundle:Admin/OrderList:shipment.html.twig', [
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
    public function shipmentDocumentAction(Request $request)
    {
        $this->isGranted('VIEW', $this->getParameter('ekyna_commerce.order_shipment.class'));

        $shipments = $this
            ->get('ekyna_commerce.order_shipment.repository')
            ->findBy(['id' => (array) $request->query->get('id')]);

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
    public function platformActionAction(Request $request)
    {
        $platformName   = $request->attributes->get('name');
        $actionName = $request->attributes->get('action');

        $shipments = []; // TODO

        try {
            $response = $this
                ->get('ekyna_commerce.shipment.gateway_registry')
                ->executePlatformAction($platformName, $actionName, $shipments);

            if (null !== $response) {
                return $response;
            }
        } catch (Exception\LogicException $e) {
            $this->addFlash('ekyna_commerce.shipment.message.unsupported_action', 'danger');
        } catch (Exception\RuntimeException $e) {
            $this->addFlash($e->getMessage(), 'danger');
        }

        if ($request->server->has('HTTP_REFERER') && !empty($referer = $request->server->get('HTTP_REFERER'))) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('ekyna_commerce_admin_order_list_shipment');
    }
}
