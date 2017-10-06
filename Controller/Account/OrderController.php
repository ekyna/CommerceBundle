<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\SaleStepValidatorInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

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

        $orders = $this
            ->get('ekyna_commerce.order.repository')
            ->findByCustomer($customer);

        return $this->render('EkynaCommerceBundle:Account/Order:index.html.twig', [
            'orders' => $orders,
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

        $orders = $this
            ->get('ekyna_commerce.order.repository')
            ->findByCustomer($customer);

        return $this->render('EkynaCommerceBundle:Account/Order:show.html.twig', [
            'order'  => $order,
            'view'   => $orderView,
            'orders' => $orders,
        ]);
    }

    /**
     * Payment action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function paymentAction(Request $request)
    {
        $customer = $this->getCustomerOrRedirect();

        $order = $this->findOrderByCustomerAndNumber($customer, $request->attributes->get('number'));

        $cancelUrl = $this->generateUrl('ekyna_commerce_account_order_show', [
            'number' => $order->getNumber(),
        ]);

        if (!$this->validateSaleStep($order, SaleStepValidatorInterface::PAYMENT_STEP)) {
            return $this->redirect($cancelUrl);
        }

        $checkout = $this->get('ekyna_commerce.checkout.payment_manager');

        $checkout->initialize($order, $this->generateUrl('ekyna_commerce_account_order_payment', [
            'number' => $order->getNumber(),
        ]));

        if (null !== $payment = $checkout->handleRequest($request)) {
            $order->addPayment($payment);

            $event = $this->get('ekyna_commerce.order.operator')->update($order);
            if ($event->isPropagationStopped() || $event->hasErrors()) {
                $event->toFlashes($this->getSession()->getFlashBag());

                return $this->redirect($cancelUrl);
            }

            return $this->redirect($this->generateUrl('ekyna_commerce_payment_order_capture', [
                'key' => $payment->getKey(),
            ]));
        }

        $orders = $this
            ->get('ekyna_commerce.order.repository')
            ->findByCustomer($customer);

        return $this->render('EkynaCommerceBundle:Account/Order:payment.html.twig', [
            'order'  => $order,
            'forms'  => $checkout->getFormsViews(),
            'orders' => $orders,
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
