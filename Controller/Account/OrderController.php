<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Form\Type\Order\OrderAttachmentType;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CoreBundle\Form\Type\ConfirmType;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\SaleStepValidatorInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentTransitions;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderPaymentInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class OrderController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderController extends AbstractController
{
    /**
     * Order index action.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $customer = $this->getCustomerOrRedirect();

        $orders = $this->findOrdersByCustomer($customer);

        return $this->render('EkynaCommerceBundle:Account/Order:index.html.twig', [
            'customer' => $customer,
            'orders'   => $orders,
        ]);
    }

    /**
     * Order show action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Request $request)
    {
        $customer = $this->getCustomerOrRedirect();

        $order = $this->findOrderByCustomerAndNumber($customer, $request->attributes->get('number'));

        $orderView = $this->get('ekyna_commerce.common.view_builder')->buildSaleView($order, [
            'taxes_view' => false,
        ]);

        $orders = $this->findOrdersByCustomer($customer);

        return $this->render('EkynaCommerceBundle:Account/Order:show.html.twig', [
            'customer'     => $customer,
            'order'        => $order,
            'view'         => $orderView,
            'orders'       => $orders,
            'route_prefix' => 'ekyna_commerce_account_order',
        ]);
    }

    /**
     * Payment create action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function paymentCreateAction(Request $request)
    {
        $customer = $this->getCustomerOrRedirect();

        $order = $this->findOrderByCustomerAndNumber($customer, $request->attributes->get('number'));

        $cancelUrl = $this->generateUrl('ekyna_commerce_account_order_show', [
            'number' => $order->getNumber(),
        ]);

        if ($customer->hasParent()) {
            $this->addFlash('ekyna_commerce.account.order.message.payment_denied', 'warning');

            return $this->redirect($cancelUrl);
        }

        if (!$this->validateSaleStep($order, SaleStepValidatorInterface::PAYMENT_STEP)) {
            return $this->redirect($cancelUrl);
        }

        $checkout = $this->get('ekyna_commerce.checkout.payment_manager');

        $checkout->initialize($order, $this->generateUrl('ekyna_commerce_account_order_payment_create', [
            'number' => $order->getNumber(),
        ]));

        /** @var OrderPaymentInterface $payment */
        if (null !== $payment = $checkout->handleRequest($request)) {
            $order->addPayment($payment);

            $event = $this->get('ekyna_commerce.order.operator')->update($order);
            if ($event->isPropagationStopped() || $event->hasErrors()) {
                $event->toFlashes($this->getSession()->getFlashBag());

                return $this->redirect($cancelUrl);
            }

            $statusUrl = $this->generateUrl(
                'ekyna_commerce_account_payment_status', [], UrlGeneratorInterface::ABSOLUTE_URL
            );

            return $this
                ->get('ekyna_commerce.payment_helper')
                ->capture($payment, $statusUrl);
        }

        $orders = $this->findOrdersByCustomer($customer);

        return $this->render('EkynaCommerceBundle:Account/Order:payment.html.twig', [
            'customer' => $customer,
            'order'    => $order,
            'forms'    => $checkout->getFormsViews(),
            'orders'   => $orders,
        ]);
    }

    /**
     * Payment cancel action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function paymentCancelAction(Request $request)
    {
        $customer = $this->getCustomerOrRedirect();

        $order = $this->findOrderByCustomerAndNumber($customer, $request->attributes->get('number'));

        $payment = $this->findPaymentByOrderAndKey($order, $request->attributes->get('key'));

        $cancelUrl = $this->generateUrl('ekyna_commerce_account_order_show', [
            'number' => $order->getNumber(),
        ]);

        if ($customer->hasParent()) {
            $this->addFlash('ekyna_commerce.account.order.message.payment_denied', 'warning');

            return $this->redirect($cancelUrl);
        }

        if (!PaymentTransitions::isUserCancellable($payment)) {
            return $this->redirect($cancelUrl);
        }

        $form = $this->createForm(ConfirmType::class, null, [
            'message'     => $this->getTranslator()->trans('ekyna_commerce.account.payment.confirm_cancel', [
                '%number%' => $payment->getNumber(),
            ]),
            'cancel_path' => $cancelUrl,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $statusUrl = $this->generateUrl(
                'ekyna_commerce_account_payment_status', [], UrlGeneratorInterface::ABSOLUTE_URL
            );

            return $this
                ->get('ekyna_commerce.payment_helper')
                ->cancel($payment, $statusUrl);
        }

        $orders = $this->findOrdersByCustomer($customer);

        return $this->render('EkynaCommerceBundle:Account/Order:payment_cancel.html.twig', [
            'customer' => $customer,
            'order'    => $order,
            'form'     => $form->createView(),
            'orders'   => $orders,
        ]);
    }

    /**
     * Order attachment download action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function shipmentDownloadAction(Request $request)
    {
        $customer = $this->getCustomerOrRedirect();

        $order = $this->findOrderByCustomerAndNumber($customer, $request->attributes->get('number'));

        $shipment = $this->findShipmentByOrderAndId($order, $request->attributes->get('id'));

        $renderer = $this
            ->get('ekyna_commerce.document.renderer_factory')
            ->createRenderer($shipment);

        return $renderer->respond($request);
    }

    /**
     * Order attachment download action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function invoiceDownloadAction(Request $request)
    {
        $customer = $this->getCustomerOrRedirect();

        $order = $this->findOrderByCustomerAndNumber($customer, $request->attributes->get('number'));

        $invoice = $this->findInvoiceByOrderAndId($order, $request->attributes->get('id'));

        $renderer = $this
            ->get('ekyna_commerce.document.renderer_factory')
            ->createRenderer($invoice);

        return $renderer->respond($request);
    }

    /**
     * Order attachment create action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function attachmentCreateAction(Request $request)
    {
        $customer = $this->getCustomerOrRedirect();

        $order = $this->findOrderByCustomerAndNumber($customer, $request->attributes->get('number'));

        /** @var \Ekyna\Component\Commerce\Order\Model\OrderAttachmentInterface $attachment */
        $attachment = $this->get('ekyna_commerce.sale_factory')->createAttachmentForSale($order);
        $attachment->setOrder($order);

        $cancelPath = $this->generateUrl('ekyna_commerce_account_order_show', [
            'number' => $order->getNumber(),
        ]);

        $form = $this->createForm(OrderAttachmentType::class, $attachment, [
            'action' => $this->generateUrl('ekyna_commerce_account_order_attachment_create', [
                'number' => $order->getNumber(),
            ]),
        ]);

        $this->createFormFooter($form, ['cancel_path' => $cancelPath]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->get('ekyna_commerce.order_attachment.operator')->create($attachment);

            $event->toFlashes($this->getFlashBag());

            if (!$event->hasErrors()) {
                return $this->redirect($cancelPath);
            }
        }

        $orders = $this->findOrdersByCustomer($customer);

        return $this->render('EkynaCommerceBundle:Account/Order:attachment_create.html.twig', [
            'customer'     => $customer,
            'route_prefix' => 'ekyna_commerce_account_order',
            'order'        => $order,
            'form'         => $form->createView(),
            'orders'       => $orders,
        ]);
    }

    /**
     * Order attachment download action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function attachmentDownloadAction(Request $request)
    {
        $customer = $this->getCustomerOrRedirect();

        $order = $this->findOrderByCustomerAndNumber($customer, $request->attributes->get('number'));

        $attachment = $this->findAttachmentByOrderAndId($order, $request->attributes->get('id'));

        $fs = $this->get('local_commerce_filesystem');
        if (!$fs->has($attachment->getPath())) {
            throw $this->createNotFoundException('File not found');
        }
        $file = $fs->get($attachment->getPath());

        $response = new Response($file->read());
        $response->setPrivate();

        $response->headers->set('Content-Type', $file->getMimetype());
        $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $attachment->guessFilename()
        );

        return $response;
    }

    /**
     * Finds the orders by customer.
     *
     * @param CustomerInterface $customer
     *
     * @return array|OrderInterface[]
     */
    protected function findOrdersByCustomer(CustomerInterface $customer)
    {
        if ($customer->hasParent()) {
            return $this
                ->get('ekyna_commerce.order.repository')
                ->findByOriginCustomer($customer);
        } else {
            return $this
                ->get('ekyna_commerce.order.repository')
                ->findByCustomer($customer);
        }
    }

    /**
     * Finds the order by customer and number.
     *
     * @param CustomerInterface $customer
     * @param string            $number
     *
     * @return OrderInterface
     */
    protected function findOrderByCustomerAndNumber(CustomerInterface $customer, $number)
    {
        $order = $this
            ->get('ekyna_commerce.order.repository')
            ->findOneByCustomerAndNumber($customer, $number);

        if (null === $order) {
            throw $this->createNotFoundException('Order not found.');
        }

        return $order;
    }

    /**
     * Finds the order by customer and number.
     *
     * @param OrderInterface $order
     * @param string         $key
     *
     * @return OrderPaymentInterface
     */
    protected function findPaymentByOrderAndKey(OrderInterface $order, $key)
    {
        $payment = $this
            ->get('ekyna_commerce.order_payment.repository')
            ->findOneBy([ // TODO repository method
                'order' => $order,
                'key'   => $key,
            ]);

        if (null === $payment) {
            throw $this->createNotFoundException('Payment not found.');
        }

        return $payment;
    }

    /**
     * Finds the attachment by order and id.
     *
     * @param OrderInterface $order
     * @param integer        $id
     *
     * @return \Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface
     */
    protected function findShipmentByOrderAndId(OrderInterface $order, $id)
    {
        $shipment = $this
            ->get('ekyna_commerce.order_shipment.repository')
            ->findOneBy([
                'order' => $order,
                'id'    => $id,
            ]);

        if (null === $shipment) {
            throw $this->createNotFoundException('Shipment not found.');
        }

        return $shipment;
    }

    /**
     * Finds the invoice by order and id.
     *
     * @param OrderInterface $order
     * @param integer        $id
     *
     * @return \Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface
     */
    protected function findInvoiceByOrderAndId(OrderInterface $order, $id)
    {
        $invoice = $this
            ->get('ekyna_commerce.order_invoice.repository')
            ->findOneBy([
                'order' => $order,
                'id'    => $id,
            ]);

        if (null === $invoice) {
            throw $this->createNotFoundException('Invoice not found.');
        }

        return $invoice;
    }

    /**
     * Finds the attachment by order and id.
     *
     * @param OrderInterface $order
     * @param integer        $id
     *
     * @return \Ekyna\Component\Commerce\Common\Model\AttachmentInterface
     */
    protected function findAttachmentByOrderAndId(OrderInterface $order, $id)
    {
        $attachment = $this
            ->get('ekyna_commerce.order_attachment.repository')
            ->findOneBy([
                'order' => $order,
                'id'    => $id,
            ]);

        if (null === $attachment) {
            throw $this->createNotFoundException('Attachment not found.');
        }

        return $attachment;
    }
}
