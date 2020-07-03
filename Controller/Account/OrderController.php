<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Form\Type\Order\OrderAttachmentType;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Service\Document\RendererFactory;
use Ekyna\Bundle\CoreBundle\Form\Type\ConfirmType;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\SaleStepValidatorInterface;
use Ekyna\Component\Commerce\Common\Export\SaleCsvExporter;
use Ekyna\Component\Commerce\Common\Export\SaleXlsExporter;
use Ekyna\Component\Commerce\Common\Model\AttachmentInterface;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\PdfException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderPaymentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Stream;
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
     * @return Response
     */
    public function indexAction(): Response
    {
        $customer = $this->getCustomerOrRedirect();

        $orders = $this->findOrdersByCustomer($customer);

        return $this->render('@EkynaCommerce/Account/Order/index.html.twig', [
            'customer' => $customer,
            'orders'   => $orders,
        ]);
    }

    /**
     * Order show action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function showAction(Request $request): Response
    {
        $customer = $this->getCustomerOrRedirect();

        $order = $this->findOrderByCustomerAndNumber($customer, $request->attributes->get('number'));

        $orderView = $this->get('ekyna_commerce.common.view_builder')->buildSaleView($order, [
            'taxes_view' => false,
        ]);

        $orders = $this->findOrdersByCustomer($customer);

        return $this->render('@EkynaCommerce/Account/Order/show.html.twig', [
            'customer'     => $customer,
            'order'        => $order,
            'view'         => $orderView,
            'orders'       => $orders,
            'route_prefix' => 'ekyna_commerce_account_order',
        ]);
    }

    /**
     * Order export.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function exportAction(Request $request): Response
    {
        $customer = $this->getCustomerOrRedirect();

        $order = $this->findOrderByCustomerAndNumber($customer, $request->attributes->get('number'));

        $format = $request->getRequestFormat('csv');
        if ($format === 'csv') {
            $exporter = $this->get(SaleCsvExporter::class);
        } elseif ($format === 'xls') {
            $exporter = $this->get(SaleXlsExporter::class);
        } else {
            throw new InvalidArgumentException("Unexpected format '$format'");
        }

        try {
            $path = $exporter->export($order);
        } catch (CommerceExceptionInterface $e) {
            if ($this->getParameter('kernel.debug')) {
                throw $e;
            }

            $this->addFlash($e->getMessage(), 'danger');

            return $this->redirect($this->generateUrl('ekyna_commerce_account_order_show', [
                'number' => $order->getNumber(),
            ]));
        }

        clearstatcache(true, $path);

        $response = new BinaryFileResponse(new Stream($path));

        $disposition = $response
            ->headers
            ->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $order->getNumber() . '.csv');

        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'text/csv');

        return $response;
    }

    /**
     * Payment create action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function paymentCreateAction(Request $request): Response
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

        $checkoutManager = $this->get('ekyna_commerce.payment.checkout_manager');

        $checkoutManager->initialize($order, $this->generateUrl('ekyna_commerce_account_order_payment_create', [
            'number' => $order->getNumber(),
        ]));

        /** @var OrderPaymentInterface $payment */
        if (null !== $payment = $checkoutManager->handleRequest($request)) {
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

        return $this->render('@EkynaCommerce/Account/Order/payment_create.html.twig', [
            'customer' => $customer,
            'order'    => $order,
            'forms'    => $checkoutManager->getFormsViews(),
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
    public function paymentCancelAction(Request $request): Response
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

        $helper = $this->get('ekyna_commerce.payment_helper');

        if (!$helper->isUserCancellable($payment)) {
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

            return $helper->cancel($payment, $statusUrl);
        }

        $orders = $this->findOrdersByCustomer($customer);

        return $this->render('@EkynaCommerce/Account/Order/payment_cancel.html.twig', [
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
    public function shipmentDownloadAction(Request $request): Response
    {
        $customer = $this->getCustomerOrRedirect();

        $order = $this->findOrderByCustomerAndNumber($customer, $request->attributes->get('number'));

        $shipment = $this->findShipmentByOrderAndId($order, $request->attributes->get('id'));

        $renderer = $this
            ->get(RendererFactory::class)
            ->createRenderer($shipment);

        try {
            return $renderer->respond($request);
        } catch (PdfException $e) {
            $this->addFlash('ekyna_commerce.document.message.failed_to_generate', 'danger');

            return $this->redirectToReferer(
                $this->generateUrl('ekyna_commerce_account_order_index')
            );
        }
    }

    /**
     * Order attachment download action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function invoiceDownloadAction(Request $request): Response
    {
        $customer = $this->getCustomerOrRedirect();

        $order = $this->findOrderByCustomerAndNumber($customer, $request->attributes->get('number'));

        $invoice = $this->findInvoiceByOrderAndId($order, $request->attributes->get('id'));

        $renderer = $this
            ->get(RendererFactory::class)
            ->createRenderer($invoice);

        try {
            return $renderer->respond($request);
        } catch (PdfException $e) {
            $this->addFlash('ekyna_commerce.document.message.failed_to_generate', 'danger');

            return $this->redirectToReferer(
                $this->generateUrl('ekyna_commerce_account_order_index')
            );
        }
    }

    /**
     * Order attachment create action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function attachmentCreateAction(Request $request): Response
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

        return $this->render('@EkynaCommerce/Account/Order/attachment_create.html.twig', [
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
    public function attachmentDownloadAction(Request $request): Response
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
    protected function findOrdersByCustomer(CustomerInterface $customer): array
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
    protected function findOrderByCustomerAndNumber(CustomerInterface $customer, string $number): OrderInterface
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
    protected function findPaymentByOrderAndKey(OrderInterface $order, string $key): OrderPaymentInterface
    {
        $payment = $this
            ->get('ekyna_commerce.order_payment.repository')
            ->findOneBy([
                'order' => $order,
                'key'   => $key,
            ]);

        if (null === $payment) {
            throw $this->createNotFoundException('Payment not found.');
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $payment;
    }

    /**
     * Finds the attachment by order and id.
     *
     * @param OrderInterface $order
     * @param int        $id
     *
     * @return ShipmentInterface
     */
    protected function findShipmentByOrderAndId(OrderInterface $order, int $id): ShipmentInterface
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

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $shipment;
    }

    /**
     * Finds the invoice by order and id.
     *
     * @param OrderInterface $order
     * @param int        $id
     *
     * @return InvoiceInterface
     */
    protected function findInvoiceByOrderAndId(OrderInterface $order, int $id): InvoiceInterface
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

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $invoice;
    }

    /**
     * Finds the attachment by order and id.
     *
     * @param OrderInterface $order
     * @param int            $id
     *
     * @return AttachmentInterface
     */
    protected function findAttachmentByOrderAndId(OrderInterface $order, int $id): AttachmentInterface
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

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $attachment;
    }
}
