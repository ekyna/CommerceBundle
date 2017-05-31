<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\CommerceBundle\Table\Type;
use Ekyna\Bundle\CoreBundle\Controller\Controller;
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

        return $this->render('EkynaCommerceBundle:Admin/OrderList:invoice.html.twig', [
            'invoices' => $table->createView(),
        ]);
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

        return $this->render('EkynaCommerceBundle:Admin/OrderList:shipment.html.twig', [
            'shipments' => $table->createView(),
        ]);
    }
}
