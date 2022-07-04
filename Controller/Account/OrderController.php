<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Form\Type\Order\OrderAttachmentType;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Bundle\CommerceBundle\Service\Document\RendererFactory;
use Ekyna\Bundle\CommerceBundle\Service\Payment\CheckoutManager;
use Ekyna\Bundle\CommerceBundle\Service\Payment\PaymentHelper;
use Ekyna\Bundle\ResourceBundle\Service\Filesystem\FilesystemHelper;
use Ekyna\Bundle\UiBundle\Form\Type\ConfirmType;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Bundle\UiBundle\Service\FlashHelper;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\SaleStepValidatorInterface;
use Ekyna\Component\Commerce\Common\Export\SaleCsvExporter;
use Ekyna\Component\Commerce\Common\Export\SaleXlsExporter;
use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Model\OrderAttachmentInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Commerce\Order\Model\OrderPaymentInterface;
use Ekyna\Component\Commerce\Order\Model\OrderShipmentInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderPaymentRepositoryInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Resource\Exception\PdfException;
use Ekyna\Component\Resource\Helper\File\File;
use Ekyna\Component\Resource\Manager\ManagerFactoryInterface;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use League\Flysystem\Filesystem;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

use function Symfony\Component\Translation\t;

/**
 * Class OrderController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderController implements ControllerInterface
{
    use CustomerTrait;

    private RepositoryFactoryInterface $repositoryFactory;
    private ManagerFactoryInterface    $managerFactory;
    private UrlGeneratorInterface      $urlGenerator;
    private Environment                $twig;

    private FactoryHelperInterface     $factoryHelper;
    private SaleStepValidatorInterface $stepValidator;
    private CheckoutManager            $checkoutManager;
    private PaymentHelper              $paymentHelper;
    private FormFactoryInterface       $formFactory;

    private FlashHelper     $flashHelper;
    private SaleCsvExporter $csvExporter;
    private SaleXlsExporter $xlsExporter;
    private RendererFactory $rendererFactory;
    private Filesystem      $filesystem;
    private ConstantsHelper $constantsHelper;
    private bool            $debug;

    public function __construct(
        RepositoryFactoryInterface $repositoryFactory,
        ManagerFactoryInterface    $managerFactory,
        UrlGeneratorInterface      $urlGenerator,
        Environment                $twig,
        FactoryHelperInterface     $factoryHelper,
        SaleStepValidatorInterface $stepValidator,
        CheckoutManager            $checkoutManager,
        PaymentHelper              $paymentHelper,
        FormFactoryInterface       $formFactory,
        FlashHelper                $flashHelper,
        SaleCsvExporter            $csvExporter,
        SaleXlsExporter            $xlsExporter,
        RendererFactory            $rendererFactory,
        Filesystem                 $filesystem,
        ConstantsHelper $constantsHelper,
        bool                       $debug
    ) {
        $this->repositoryFactory = $repositoryFactory;
        $this->managerFactory = $managerFactory;
        $this->urlGenerator = $urlGenerator;
        $this->twig = $twig;
        $this->factoryHelper = $factoryHelper;
        $this->stepValidator = $stepValidator;
        $this->checkoutManager = $checkoutManager;
        $this->paymentHelper = $paymentHelper;
        $this->formFactory = $formFactory;
        $this->flashHelper = $flashHelper;
        $this->csvExporter = $csvExporter;
        $this->xlsExporter = $xlsExporter;
        $this->rendererFactory = $rendererFactory;
        $this->filesystem = $filesystem;
        $this->constantsHelper = $constantsHelper;
        $this->debug = $debug;
    }


    public function index(): Response
    {
        $customer = $this->getCustomer();

        $orders = $this->findOrdersByCustomer($customer);

        $content = $this->twig->render('@EkynaCommerce/Account/Order/index.html.twig', [
            'customer' => $customer,
            'orders'   => $orders,
        ]);

        return (new Response($content))->setPrivate();
    }

    public function read(Request $request): Response
    {
        $customer = $this->getCustomer();

        $order = $this->findOrderByCustomerAndNumber($customer, $request->attributes->get('number'));

        $orders = $this->findOrdersByCustomer($customer);

        $content = $this->twig->render('@EkynaCommerce/Account/Order/show.html.twig', [
            'customer'     => $customer,
            'order'        => $order,
            'orders'       => $orders,
            'route_prefix' => 'ekyna_commerce_account_order',
        ]);

        return (new Response($content))->setPrivate();
    }

    public function export(Request $request): Response
    {
        $customer = $this->getCustomer();

        $order = $this->findOrderByCustomerAndNumber($customer, $request->attributes->get('number'));

        $format = $request->getRequestFormat('csv');
        if ($format === 'csv') {
            $exporter = $this->csvExporter;
            $mimeType = 'text/csv';
        } elseif ($format === 'xls') {
            $exporter = $this->xlsExporter;
            $mimeType = 'application/vnd.ms-excel';
        } else {
            throw new InvalidArgumentException("Unexpected format '$format'");
        }

        try {
            $path = $exporter->export($order);
        } catch (CommerceExceptionInterface $e) {
            if ($this->debug) {
                throw $e;
            }

            $this->flashHelper->addFlash(t($e->getMessage(), [], 'EkynaCommerce'), 'danger');

            $redirect = $this->urlGenerator->generate('ekyna_commerce_account_order_read', [
                'number' => $order->getNumber(),
            ]);

            return new RedirectResponse($redirect);
        }

        return File::buildResponse($path, [
            'file_name' => $order->getNumber() . '.' . $format,
            'mime_type' => $mimeType,
        ]);
    }

    public function paymentCreate(Request $request): Response
    {
        $customer = $this->getCustomer();

        $order = $this->findOrderByCustomerAndNumber($customer, $request->attributes->get('number'));

        $redirect = $this->urlGenerator->generate('ekyna_commerce_account_order_read', [
            'number' => $order->getNumber(),
        ]);

        if ($customer->hasParent()) {
            $message = t('account.order.message.payment_denied', [
                '{identity}' => $this->constantsHelper->renderIdentity($customer->getParent()),
            ], 'EkynaCommerce');

            $this->flashHelper->addFlash($message, 'warning');

            return new RedirectResponse($redirect);
        }

        if (!$this->stepValidator->validate($order, SaleStepValidatorInterface::PAYMENT_STEP)) {
            return new RedirectResponse($redirect);
        }

        $action = $this->urlGenerator->generate('ekyna_commerce_account_order_payment_create', [
            'number' => $order->getNumber(),
        ]);

        $this->checkoutManager->initialize($order, $action);

        /** @var OrderPaymentInterface $payment */
        if ($payment = $this->checkoutManager->handleRequest($request)) {
            $order->addPayment($payment);

            $event = $this->managerFactory->getManager(OrderInterface::class)->update($order);
            if ($event->isPropagationStopped() || $event->hasErrors()) {
                $this->flashHelper->fromEvent($event);

                return new RedirectResponse($redirect);
            }

            $statusUrl = $this->urlGenerator->generate(
                'ekyna_commerce_account_payment_status', [], UrlGeneratorInterface::ABSOLUTE_URL
            );

            return $this
                ->paymentHelper
                ->capture($payment, $statusUrl);
        }

        $orders = $this->findOrdersByCustomer($customer);

        $content = $this->twig->render('@EkynaCommerce/Account/Order/payment_create.html.twig', [
            'customer' => $customer,
            'order'    => $order,
            'forms'    => $this->checkoutManager->getFormsViews(),
            'orders'   => $orders,
        ]);

        return (new Response($content))->setPrivate();
    }

    public function paymentCancel(Request $request): Response
    {
        $customer = $this->getCustomer();

        $order = $this->findOrderByCustomerAndNumber($customer, $request->attributes->get('number'));

        $payment = $this->findPaymentByOrderAndKey($order, $request->attributes->get('key'));

        $redirect = $this->urlGenerator->generate('ekyna_commerce_account_order_read', [
            'number' => $order->getNumber(),
        ]);

        if (!$this->paymentHelper->isUserCancellable($payment)) {
            return new RedirectResponse($redirect);
        }

        $form = $this->formFactory->create(ConfirmType::class, null, [
            'message'     => t('account.payment.confirm_cancel', [
                '%number%' => $payment->getNumber(),
            ], 'EkynaCommerce'),
            'cancel_path' => $redirect,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $statusUrl = $this->urlGenerator->generate(
                'ekyna_commerce_account_payment_status', [], UrlGeneratorInterface::ABSOLUTE_URL
            );

            return $this->paymentHelper->cancel($payment, $statusUrl);
        }

        $orders = $this->findOrdersByCustomer($customer);

        $content = $this->twig->render('@EkynaCommerce/Account/Order/payment_cancel.html.twig', [
            'customer' => $customer,
            'order'    => $order,
            'form'     => $form->createView(),
            'orders'   => $orders,
        ]);

        return (new Response($content))->setPrivate();
    }

    public function shipmentDownload(Request $request): Response
    {
        $customer = $this->getCustomer();

        $order = $this->findOrderByCustomerAndNumber($customer, $request->attributes->get('number'));

        $shipment = $this->findShipmentByOrderAndId($order, $request->attributes->getInt('id'));

        $renderer = $this
            ->rendererFactory
            ->createRenderer($shipment);

        try {
            return $renderer->respond($request);
        } catch (PdfException $e) {
            $this->flashHelper->addFlash(t('document.message.failed_to_generate', [], 'EkynaCommerce'), 'danger');

            return new RedirectResponse(
                $this->urlGenerator->generate('ekyna_commerce_account_order_index')
            );
        }
    }

    public function invoiceDownload(Request $request): Response
    {
        $customer = $this->getCustomer();

        $order = $this->findOrderByCustomerAndNumber($customer, $request->attributes->get('number'));

        $invoice = $this->findInvoiceByOrderAndId($order, $request->attributes->getInt('id'));

        $renderer = $this
            ->rendererFactory
            ->createRenderer($invoice);

        try {
            return $renderer->respond($request);
        } catch (PdfException $e) {
            $this->flashHelper->addFlash(t('document.message.failed_to_generate', [], 'EkynaCommerce'), 'danger');

            return new RedirectResponse(
                $this->urlGenerator->generate('ekyna_commerce_account_order_index')
            );
        }
    }

    public function attachmentCreate(Request $request): Response
    {
        $customer = $this->getCustomer();

        $order = $this->findOrderByCustomerAndNumber($customer, $request->attributes->get('number'));

        /** @var OrderAttachmentInterface $attachment */
        $attachment = $this->factoryHelper->createAttachmentForSale($order);
        $attachment->setOrder($order);

        $redirect = $this->urlGenerator->generate('ekyna_commerce_account_order_read', [
            'number' => $order->getNumber(),
        ]);

        $form = $this->formFactory->create(OrderAttachmentType::class, $attachment, [
            'action' => $this->urlGenerator->generate('ekyna_commerce_account_order_attachment_create', [
                'number' => $order->getNumber(),
            ]),
        ]);

        FormUtil::addFooter($form, ['cancel_path' => $redirect]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->managerFactory->getManager(OrderAttachmentInterface::class)->create($attachment);

            $this->flashHelper->fromEvent($event);

            if (!$event->hasErrors()) {
                return new RedirectResponse($redirect);
            }
        }

        $orders = $this->findOrdersByCustomer($customer);

        $content = $this->twig->render('@EkynaCommerce/Account/Order/attachment_create.html.twig', [
            'customer'     => $customer,
            'route_prefix' => 'ekyna_commerce_account_order',
            'order'        => $order,
            'form'         => $form->createView(),
            'orders'       => $orders,
        ]);

        return (new Response($content))->setPrivate();
    }

    public function attachmentDownload(Request $request): Response
    {
        $customer = $this->getCustomer();

        $order = $this->findOrderByCustomerAndNumber($customer, $request->attributes->get('number'));

        $attachment = $this->findAttachmentByOrderAndId($order, $request->attributes->getInt('id'));

        $fs = new FilesystemHelper($this->filesystem);
        if (!$fs->fileExists($attachment->getPath(), false)) {
            throw new NotFoundHttpException('File not found');
        }

        return $fs->createFileResponse($attachment->getPath(), $request, true);
    }

    /**
     * @return array<OrderInterface>
     */
    private function findOrdersByCustomer(CustomerInterface $customer): array
    {
        /** @var OrderRepositoryInterface $repository */
        $repository = $this->repositoryFactory->getRepository(OrderInterface::class);

        if ($customer->hasParent()) {
            return $repository->findByOriginCustomer($customer);
        }

        return $repository->findByCustomer($customer);
    }

    private function findOrderByCustomerAndNumber(CustomerInterface $customer, string $number): OrderInterface
    {
        /** @var OrderRepositoryInterface $repository */
        $repository = $this->repositoryFactory->getRepository(OrderInterface::class);

        $order = $repository->findOneByCustomerAndNumber($customer, $number);

        if (!$order) {
            throw new NotFoundHttpException('Order not found.');
        }

        return $order;
    }

    private function findPaymentByOrderAndKey(OrderInterface $order, string $key): OrderPaymentInterface
    {
        /** @var OrderPaymentRepositoryInterface $repository */
        $repository = $this->repositoryFactory->getRepository(OrderPaymentInterface::class);

        $payment = $repository->findOneByOrderAndKey($order, $key);

        if (!$payment) {
            throw new NotFoundHttpException('Payment not found.');
        }

        return $payment;
    }

    private function findShipmentByOrderAndId(OrderInterface $order, int $id): OrderShipmentInterface
    {
        $shipment = $this
            ->repositoryFactory
            ->getRepository(OrderShipmentInterface::class)
            ->findOneBy([
                'order' => $order,
                'id'    => $id,
            ]);

        if (null === $shipment) {
            throw new NotFoundHttpException('Shipment not found.');
        }

        return $shipment;
    }

    private function findInvoiceByOrderAndId(OrderInterface $order, int $id): OrderInvoiceInterface
    {
        $invoice = $this
            ->repositoryFactory
            ->getRepository(OrderInvoiceInterface::class)
            ->findOneBy([
                'order' => $order,
                'id'    => $id,
            ]);

        if (null === $invoice) {
            throw new NotFoundHttpException('Invoice not found.');
        }

        return $invoice;
    }

    private function findAttachmentByOrderAndId(OrderInterface $order, int $id): OrderAttachmentInterface
    {
        $attachment = $this
            ->repositoryFactory
            ->getRepository(OrderAttachmentInterface::class)
            ->findOneBy([
                'order' => $order,
                'id'    => $id,
            ]);

        if (null === $attachment) {
            throw new NotFoundHttpException('Attachment not found.');
        }

        return $attachment;
    }
}
