<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Form\Type\Account\QuoteVoucherType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Quote\QuoteAddressType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Quote\QuoteAttachmentType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleAddressType;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Model\DocumentTypes as BDocumentTypes;
use Ekyna\Bundle\CommerceBundle\Model\QuoteVoucher;
use Ekyna\Bundle\CommerceBundle\Service\Common\SaleViewHelper;
use Ekyna\Bundle\CommerceBundle\Service\Payment\CheckoutManager;
use Ekyna\Bundle\CommerceBundle\Service\Payment\PaymentHelper;
use Ekyna\Bundle\ResourceBundle\Service\Filesystem\FilesystemHelper;
use Ekyna\Bundle\UiBundle\Form\Type\ConfirmType;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Bundle\UiBundle\Service\FlashHelper;
use Ekyna\Bundle\UiBundle\Service\Modal\ModalRenderer;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\SaleStepValidatorInterface;
use Ekyna\Component\Commerce\Common\Export\SaleCsvExporter;
use Ekyna\Component\Commerce\Common\Export\SaleXlsExporter;
use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Updater\SaleUpdaterInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentTypes as CDocumentTypes;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Quote\Model\QuoteAttachmentInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Quote\Model\QuotePaymentInterface;
use Ekyna\Component\Commerce\Quote\Repository\QuotePaymentRepositoryInterface;
use Ekyna\Component\Commerce\Quote\Repository\QuoteRepositoryInterface;
use Ekyna\Component\Resource\Helper\File\File;
use Ekyna\Component\Resource\Manager\ManagerFactoryInterface;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use League\Flysystem\Filesystem;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

use function Symfony\Component\Translation\t;

/**
 * Class QuoteController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteController implements ControllerInterface
{
    use CustomerTrait;
    use QuoteTrait;

    private SaleUpdaterInterface       $saleUpdater;
    private SaleFactoryInterface       $saleFactory;
    private SaleStepValidatorInterface $stepValidator;
    private CheckoutManager            $checkoutManager;
    private PaymentHelper              $paymentHelper;
    private TranslatorInterface        $translator;
    private FormFactoryInterface       $formFactory;
    private FlashHelper                $flashHelper;
    private SaleCsvExporter            $csvExporter;
    private SaleXlsExporter            $xlsExporter;
    private Filesystem                 $filesystem;
    private ModalRenderer              $modalRenderer;
    private bool                       $debug;

    public function __construct(
        // Quote trait
        RepositoryFactoryInterface $repositoryFactory,
        ManagerFactoryInterface    $managerFactory,
        SaleViewHelper             $saleViewHelper,
        UrlGeneratorInterface      $urlGenerator,
        Environment                $twig,
        // This
        SaleUpdaterInterface       $saleUpdater,
        SaleFactoryInterface       $saleFactory,
        SaleStepValidatorInterface $stepValidator,
        CheckoutManager            $checkoutManager,
        PaymentHelper              $paymentHelper,
        TranslatorInterface        $translator,
        FormFactoryInterface       $formFactory,
        FlashHelper                $flashHelper,
        SaleCsvExporter            $csvExporter,
        SaleXlsExporter            $xlsExporter,
        Filesystem                 $filesystem,
        ModalRenderer              $modalRenderer,
        bool                       $debug
    ) {
        // Quote trait
        $this->repositoryFactory = $repositoryFactory;
        $this->managerFactory = $managerFactory;
        $this->saleViewHelper = $saleViewHelper;
        $this->urlGenerator = $urlGenerator;
        $this->twig = $twig;

        // This
        $this->saleUpdater = $saleUpdater;
        $this->saleFactory = $saleFactory;
        $this->stepValidator = $stepValidator;
        $this->checkoutManager = $checkoutManager;
        $this->paymentHelper = $paymentHelper;
        $this->translator = $translator;
        $this->formFactory = $formFactory;
        $this->flashHelper = $flashHelper;
        $this->csvExporter = $csvExporter;
        $this->xlsExporter = $xlsExporter;
        $this->filesystem = $filesystem;
        $this->modalRenderer = $modalRenderer;
        $this->debug = $debug;
    }

    public function index(): Response
    {
        $customer = $this->getCustomer();

        $quotes = $this->findQuotesByCustomer($customer);

        $content = $this->twig->render('@EkynaCommerce/Account/Quote/index.html.twig', [
            'customer' => $customer,
            'quotes'   => $quotes,
        ]);

        return (new Response($content))->setPrivate();
    }

    public function read(Request $request): Response
    {
        $customer = $this->getCustomer();

        $quote = $this->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        $quoteView = $this->buildSaleView($quote);

        $quotes = $this->findQuotesByCustomer($customer);

        $content = $this->twig->render('@EkynaCommerce/Account/Quote/show.html.twig', [
            'customer'     => $customer,
            'quote'        => $quote,
            'view'         => $quoteView,
            'quotes'       => $quotes,
            'route_prefix' => 'ekyna_commerce_account_quote',
        ]);

        return (new Response($content))->setPrivate();
    }

    public function export(Request $request): Response
    {
        $customer = $this->getCustomer();

        $quote = $this->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

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
            $path = $exporter->export($quote);
        } catch (CommerceExceptionInterface $e) {
            if ($this->debug) {
                throw $e;
            }

            $this->flashHelper->addFlash(t($e->getMessage(), [], 'EkynaCommerce'), 'danger');

            $redirect = $this->urlGenerator->generate('ekyna_commerce_account_quote_read', [
                'number' => $quote->getNumber(),
            ]);

            return new RedirectResponse($redirect);
        }

        return File::buildResponse($path, [
            'file_name' => $quote->getNumber() . '.' . $format,
            'mime_type' => $mimeType,
        ]);
    }

    public function refresh(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('');
        }

        $customer = $this->getCustomer();

        $quote = $this->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        return $this->buildXhrSaleViewResponse($quote);
    }

    public function recalculate(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('');
        }

        $customer = $this->getCustomer();

        $quote = $this->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        $form = $this->buildQuantitiesForm($quote);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO recalculate may return false if nothing changed even if quantities are different (sample case)
            if ($this->saleUpdater->recalculate($quote)) {
                $event = $this->managerFactory->getManager(QuoteInterface::class)->save($quote);

                // TODO Some important information to display may have changed (state, etc)

                if ($request->isXmlHttpRequest()) {
                    if ($event->hasErrors()) {
                        foreach ($event->getErrors() as $error) {
                            $form->addError(new FormError($error->getMessage()));
                        }
                    }
                }
            }
        }

        return $this->buildXhrSaleViewResponse($quote, $form);
    }

    public function voucher(Request $request): Response
    {
        $customer = $this->getCustomer();

        $quote = $this->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        $redirect = $this->urlGenerator->generate('ekyna_commerce_account_quote_read', [
            'number' => $quote->getNumber(),
        ]);

        if ($customer->hasParent()) {
            $this->flashHelper->addFlash(t('account.quote.message.voucher_denied', [], 'EkynaCommerce'), 'warning');

            return new RedirectResponse($redirect);
        }

        // Create voucher attachment if not exists
        if (null === $attachment = $quote->getVoucherAttachment()) {
            $attachment = $this
                ->saleFactory
                ->createAttachmentForSale($quote);

            $type = CDocumentTypes::TYPE_VOUCHER;

            $attachment
                ->setType(CDocumentTypes::TYPE_VOUCHER)
                ->setTitle(BDocumentTypes::getLabel($type)->trans($this->translator));

            $quote->addAttachment($attachment);
        }

        $voucher = new QuoteVoucher();
        $voucher
            ->setNumber($quote->getVoucherNumber())
            ->setAttachment($attachment);

        $form = $this->formFactory->create(QuoteVoucherType::class, $voucher, [
            'action' => $this->urlGenerator->generate('ekyna_commerce_account_quote_voucher', [
                'number' => $quote->getNumber(),
            ]),
        ]);

        FormUtil::addFooter($form, ['cancel_path' => $redirect]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $quote->setVoucherNumber($voucher->getNumber());
            $event = $this->managerFactory->getManager(QuoteInterface::class)->update($quote);

            $this->flashHelper->fromEvent($event);

            if (!$event->hasErrors()) {
                return new RedirectResponse($redirect);
            }
        }

        $quotes = $this->findQuotesByCustomer($customer);

        $content = $this->twig->render('@EkynaCommerce/Account/Quote/voucher.html.twig', [
            'customer'     => $customer,
            'route_prefix' => 'ekyna_commerce_account_quote',
            'quote'        => $quote,
            'form'         => $form->createView(),
            'quotes'       => $quotes,
        ]);

        return (new Response($content))->setPrivate();
    }

    public function paymentCreate(Request $request): Response
    {
        $customer = $this->getCustomer();

        $quote = $this->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        $redirect = $this->urlGenerator->generate('ekyna_commerce_account_quote_read', [
            'number' => $quote->getNumber(),
        ]);

        if ($customer->hasParent()) {
            $this->flashHelper->addFlash(t('account.quote.message.payment_denied', [], 'EkynaCommerce'), 'warning');

            return new RedirectResponse($redirect);
        }

        if (!$this->stepValidator->validate($quote, SaleStepValidatorInterface::PAYMENT_STEP)) {
            return new RedirectResponse($redirect);
        }

        $action = $this->urlGenerator->generate('ekyna_commerce_account_quote_payment_create', [
            'number' => $quote->getNumber(),
        ]);

        $this->checkoutManager->initialize($quote, $action);

        /** @var QuotePaymentInterface $payment */
        if ($payment = $this->checkoutManager->handleRequest($request)) {
            $quote->addPayment($payment);

            $event = $this->managerFactory->getManager(QuoteInterface::class)->update($quote);
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

        $quotes = $this->findQuotesByCustomer($customer);

        $content = $this->twig->render('@EkynaCommerce/Account/Quote/payment_create.html.twig', [
            'customer' => $customer,
            'quote'    => $quote,
            'forms'    => $this->checkoutManager->getFormsViews(),
            'quotes'   => $quotes,
        ]);

        return (new Response($content))->setPrivate();
    }

    public function paymentCancel(Request $request): Response
    {
        $customer = $this->getCustomer();

        $quote = $this->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        $payment = $this->findPaymentByQuoteAndKey($quote, $request->attributes->get('key'));

        $redirect = $this->urlGenerator->generate('ekyna_commerce_account_quote_read', [
            'number' => $quote->getNumber(),
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

        $quotes = $this->findQuotesByCustomer($customer);

        $content = $this->twig->render('@EkynaCommerce/Account/Quote/payment_cancel.html.twig', [
            'customer' => $customer,
            'quote'    => $quote,
            'form'     => $form->createView(),
            'quotes'   => $quotes,
        ]);

        return (new Response($content))->setPrivate();
    }

    public function attachmentCreate(Request $request): Response
    {
        $customer = $this->getCustomer();

        $quote = $this->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        /** @var QuoteAttachmentInterface $attachment */
        $attachment = $this->saleFactory->createAttachmentForSale($quote);
        $attachment->setQuote($quote);

        $redirect = $this->urlGenerator->generate('ekyna_commerce_account_quote_read', [
            'number' => $quote->getNumber(),
        ]);

        $form = $this->formFactory->create(QuoteAttachmentType::class, $attachment, [
            'action' => $this->urlGenerator->generate('ekyna_commerce_account_quote_attachment_create', [
                'number' => $quote->getNumber(),
            ]),
        ]);

        FormUtil::addFooter($form, ['cancel_path' => $redirect]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->managerFactory->getManager(QuoteAttachmentInterface::class)->create($attachment);

            $this->flashHelper->fromEvent($event);

            if (!$event->hasErrors()) {
                return new RedirectResponse($redirect);
            }
        }

        $quotes = $this->findQuotesByCustomer($customer);

        $content = $this->twig->render('@EkynaCommerce/Account/Quote/attachment_create.html.twig', [
            'customer'     => $customer,
            'route_prefix' => 'ekyna_commerce_account_quote',
            'quote'        => $quote,
            'form'         => $form->createView(),
            'quotes'       => $quotes,
        ]);

        return (new Response($content))->setPrivate();
    }

    public function attachmentDownload(Request $request): Response
    {
        $customer = $this->getCustomer();

        $quote = $this->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        $attachment = $this->findAttachmentByQuoteAndId($quote, $request->attributes->getInt('id'));

        $fs = new FilesystemHelper($this->filesystem);
        if (!$fs->fileExists($attachment->getPath(), false)) {
            throw new NotFoundHttpException('File not found');
        }

        return $fs->createFileResponse($attachment->getPath(), $request, true);
    }

    /**
     * Change invoice address.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function invoiceAddress(Request $request): Response
    {
        return $this->handleForm(
            $request,
            SaleAddressType::class,
            'checkout.button.edit_invoice',
            'ekyna_commerce_account_quote_invoice_address',
            [
                'address_type'      => QuoteAddressType::class,
                'validation_groups' => ['Address'],
            ]
        );
    }

    /**
     * Change delivery address.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function deliveryAddress(Request $request): Response
    {
        return $this->handleForm(
            $request,
            SaleAddressType::class,
            'checkout.button.edit_invoice',
            'ekyna_commerce_account_quote_delivery_address',
            [
                'address_type'      => QuoteAddressType::class,
                'validation_groups' => ['Address'],
                'delivery'          => true,
            ]
        );
    }

    /**
     * Handle form.
     *
     * @param Request $request
     * @param string  $type
     * @param string  $title
     * @param string  $route
     * @param array   $options
     *
     * @return Response
     */
    public function handleForm(
        Request $request,
        string  $type,
        string  $title,
        string  $route,
        array   $options = []
    ): Response {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Not yet implemented.');
        }

        $customer = $this->getCustomer();

        $quote = $this->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        if (!$quote->isEditable()) {
            throw new AccessDeniedHttpException('Quote is not editable.');
        }

        $action = $this->urlGenerator->generate($route, ['number' => $quote->getNumber()]);

        $form = $this->formFactory->create($type, $quote, array_replace([
            'method' => 'post',
            'action' => $action,
            'attr'   => [
                'class' => 'form-horizontal',
            ],
        ], $options));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->managerFactory->getManager(QuoteInterface::class)->update($quote);

            if (!$event->hasErrors()) {
                return $this->buildXhrSaleViewResponse($quote);
            }

            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        $modal = new Modal();
        $modal
            ->setTitle($title)
            ->setDomain('EkynaCommerce')
            ->setForm($form->createView())
            ->addButton(array_replace(Modal::BTN_SUBMIT, [
                'label' => 'button.save',
            ]))
            ->addButton(Modal::BTN_CANCEL);

        return $this->modalRenderer->render($modal);
    }

    /**
     * @return array<QuoteInterface>
     */
    protected function findQuotesByCustomer(CustomerInterface $customer): array
    {
        /** @var QuoteRepositoryInterface $repository */
        $repository = $this->repositoryFactory->getRepository(QuoteInterface::class);

        return $repository->findByCustomer($customer, [], true);
    }

    protected function findQuoteByCustomerAndNumber(CustomerInterface $customer, string $number): ?QuoteInterface
    {
        /** @var QuoteRepositoryInterface $repository */
        $repository = $this->repositoryFactory->getRepository(QuoteInterface::class);

        $quote = $repository->findOneByCustomerAndNumber($customer, $number);

        if (!$quote) {
            throw new NotFoundHttpException('Quote not found.');
        }

        return $quote;
    }

    protected function findPaymentByQuoteAndKey(QuoteInterface $quote, string $key): ?QuotePaymentInterface
    {
        /** @var QuotePaymentRepositoryInterface $repository */
        $repository = $this->repositoryFactory->getRepository(QuotePaymentInterface::class);

        $payment = $repository->findOneByQuoteAndKey($quote, $key);

        if (!$payment) {
            throw new NotFoundHttpException('Payment not found.');
        }

        return $payment;
    }

    protected function findAttachmentByQuoteAndId(QuoteInterface $quote, int $id): ?QuoteAttachmentInterface
    {
        $attachment = $this
            ->repositoryFactory
            ->getRepository(QuoteAttachmentInterface::class)
            ->findOneBy([
                'quote' => $quote,
                'id'    => $id,
            ]);

        if (!$attachment) {
            throw new NotFoundHttpException('Attachment not found.');
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $attachment;
    }
}
