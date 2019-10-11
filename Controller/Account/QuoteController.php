<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Form\Type\Account\QuoteVoucherType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Quote\QuoteAddressType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Quote\QuoteAttachmentType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleAddressType;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Model\DocumentTypes as BDocumentTypes;
use Ekyna\Bundle\CommerceBundle\Model\QuoteVoucher;
use Ekyna\Bundle\CoreBundle\Form\Type\ConfirmType;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\SaleStepValidatorInterface;
use Ekyna\Component\Commerce\Common\Export\SaleExporter;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentTypes as CDocumentTypes;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentTransitions;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Quote\Model\QuotePaymentInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Stream;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class QuoteController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteController extends AbstractSaleController
{
    /**
     * Quote index action.
     *
     * @return Response
     */
    public function indexAction()
    {
        $customer = $this->getCustomerOrRedirect();

        $quotes = $this->findQuotesByCustomer($customer);

        return $this->render('@EkynaCommerce/Account/Quote/index.html.twig', [
            'customer' => $customer,
            'quotes'   => $quotes,
        ]);
    }

    /**
     * Quote show action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function showAction(Request $request)
    {
        $customer = $this->getCustomerOrRedirect();

        $quote = $this->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        $quoteView = $this->buildSaleView($quote);

        $quotes = $this->findQuotesByCustomer($customer);

        return $this->render('@EkynaCommerce/Account/Quote/show.html.twig', [
            'customer'     => $customer,
            'quote'        => $quote,
            'view'         => $quoteView,
            'quotes'       => $quotes,
            'route_prefix' => 'ekyna_commerce_account_quote',
        ]);
    }

    /**
     * Quote export.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function exportAction(Request $request): Response
    {
        $customer = $this->getCustomerOrRedirect();

        $quote = $this->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        try {
            $path = $this->get(SaleExporter::class)->export($quote);
        } catch (CommerceExceptionInterface $e) {
            if ($this->getParameter('kernel.debug')) {
                throw $e;
            }

            $this->addFlash($e->getMessage(), 'danger');

            return $this->redirect($this->generateUrl('ekyna_commerce_account_quote_show', [
                'number' => $quote->getNumber(),
            ]));
        }

        clearstatcache(true, $path);

        $response = new BinaryFileResponse(new Stream($path));

        $disposition = $response
            ->headers
            ->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $quote->getNumber() . '.csv');

        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'text/csv');

        return $response;
    }

    /**
     * Refresh quote.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function refreshAction(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException("Not yet implemented");
        }

        $customer = $this->getCustomerOrRedirect();

        $quote = $this->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        return $this->buildXhrSaleViewResponse($quote);
    }

    /**
     * Recalculate quote.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function recalculateAction(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException("Not yet implemented");
        }

        $customer = $this->getCustomerOrRedirect();

        $quote = $this->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        $form = $this->buildQuantitiesForm($quote);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO recalculate may return false if nothing changed even if quantities are different (sample case)
            if ($this->get('ekyna_commerce.sale_updater')->recalculate($quote)) {
                $event = $this->get('ekyna_commerce.quote.operator')->update($quote);

                // TODO Some important information to display may have changed (state, etc)

                if ($event->hasErrors()) {
                    foreach ($event->getErrors() as $error) {
                        $form->addError(new FormError($error->getMessage()));
                    }
                }
            }
        }

        return $this->buildXhrSaleViewResponse($quote, $form);
    }

    /**
     * Voucher action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function voucherAction(Request $request)
    {
        $customer = $this->getCustomerOrRedirect();

        $quote = $this->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        $cancelUrl = $this->generateUrl('ekyna_commerce_account_quote_show', [
            'number' => $quote->getNumber(),
        ]);

        if ($customer->hasParent()) {
            $this->addFlash('ekyna_commerce.account.quote.message.voucher_denied', 'warning');

            return $this->redirect($cancelUrl);
        }

        // Create voucher attachment if not exists
        if (null === $attachment = $quote->getVoucherAttachment()) {
            $attachment = $this
                ->get('ekyna_commerce.sale_factory')
                ->createAttachmentForSale($quote);

            $type = CDocumentTypes::TYPE_VOUCHER;

            $attachment
                ->setType(CDocumentTypes::TYPE_VOUCHER)
                ->setTitle($this->getTranslator()->trans(BDocumentTypes::getLabel($type)));

            $quote->addAttachment($attachment);
        }

        $voucher = new QuoteVoucher();
        $voucher
            ->setNumber($quote->getVoucherNumber())
            ->setAttachment($attachment);

        $form = $this->createForm(QuoteVoucherType::class, $voucher, [
            'action' => $this->generateUrl('ekyna_commerce_account_quote_voucher', [
                'number' => $quote->getNumber(),
            ]),
        ]);

        $this->createFormFooter($form, ['cancel_path' => $cancelUrl]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $quote->setVoucherNumber($voucher->getNumber());
            $event = $this->get('ekyna_commerce.quote.operator')->update($quote);

            $event->toFlashes($this->getFlashBag());

            if (!$event->hasErrors()) {
                return $this->redirect($cancelUrl);
            }
        }

        $quotes = $this->findQuotesByCustomer($customer);

        return $this->render('@EkynaCommerce/Account/Quote/voucher.html.twig', [
            'customer'     => $customer,
            'route_prefix' => 'ekyna_commerce_account_quote',
            'quote'        => $quote,
            'form'         => $form->createView(),
            'quotes'       => $quotes,
        ]);
    }

    /**
     * Payment create action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function paymentCreateAction(Request $request)
    {
        $customer = $this->getCustomerOrRedirect();

        $quote = $this->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        $cancelUrl = $this->generateUrl('ekyna_commerce_account_quote_show', [
            'number' => $quote->getNumber(),
        ]);

        if ($customer->hasParent()) {
            $this->addFlash('ekyna_commerce.account.quote.message.payment_denied', 'warning');

            return $this->redirect($cancelUrl);
        }

        if (!$this->validateSaleStep($quote, SaleStepValidatorInterface::PAYMENT_STEP)) {
            return $this->redirect($cancelUrl);
        }

        $checkoutManager = $this->get('ekyna_commerce.payment.checkout_manager');

        $checkoutManager->initialize($quote, $this->generateUrl('ekyna_commerce_account_quote_payment_create', [
            'number' => $quote->getNumber(),
        ]));

        /** @var QuotePaymentInterface $payment */
        if (null !== $payment = $checkoutManager->handleRequest($request)) {
            $quote->addPayment($payment);

            $event = $this->get('ekyna_commerce.quote.operator')->update($quote);
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

        $quotes = $this->findQuotesByCustomer($customer);

        return $this->render('@EkynaCommerce/Account/Quote/payment_create.html.twig', [
            'customer' => $customer,
            'quote'    => $quote,
            'forms'    => $checkoutManager->getFormsViews(),
            'quotes'   => $quotes,
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

        $quote = $this->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        $payment = $this->findPaymentByQuoteAndKey($quote, $request->attributes->get('key'));

        $cancelUrl = $this->generateUrl('ekyna_commerce_account_quote_show', [
            'number' => $quote->getNumber(),
        ]);

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

        $quotes = $this->findQuotesByCustomer($customer);

        return $this->render('@EkynaCommerce/Account/Quote/payment_cancel.html.twig', [
            'customer' => $customer,
            'quote'    => $quote,
            'form'     => $form->createView(),
            'quotes'   => $quotes,
        ]);
    }

    /**
     * Quote attachment create action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function attachmentCreateAction(Request $request)
    {
        $customer = $this->getCustomerOrRedirect();

        $quote = $this->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        /** @var \Ekyna\Component\Commerce\Quote\Model\QuoteAttachmentInterface $attachment */
        $attachment = $this->get('ekyna_commerce.sale_factory')->createAttachmentForSale($quote);
        $attachment->setQuote($quote);

        $cancelUrl = $this->generateUrl('ekyna_commerce_account_quote_show', [
            'number' => $quote->getNumber(),
        ]);

        $form = $this->createForm(QuoteAttachmentType::class, $attachment, [
            'action' => $this->generateUrl('ekyna_commerce_account_quote_attachment_create', [
                'number' => $quote->getNumber(),
            ]),
        ]);

        $this->createFormFooter($form, ['cancel_path' => $cancelUrl]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->get('ekyna_commerce.quote_attachment.operator')->create($attachment);

            $event->toFlashes($this->getFlashBag());

            if (!$event->hasErrors()) {
                return $this->redirect($cancelUrl);
            }
        }

        $quotes = $this->findQuotesByCustomer($customer);

        return $this->render('@EkynaCommerce/Account/Quote/attachment_create.html.twig', [
            'customer'     => $customer,
            'route_prefix' => 'ekyna_commerce_account_quote',
            'quote'        => $quote,
            'form'         => $form->createView(),
            'quotes'       => $quotes,
        ]);
    }

    /**
     * Quote attachment download action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function attachmentDownloadAction(Request $request)
    {
        $customer = $this->getCustomerOrRedirect();

        $quote = $this->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        $attachment = $this->findAttachmentByQuoteAndId($quote, $request->attributes->get('id'));

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
     * Change invoice address.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function invoiceAddressAction(Request $request): Response
    {
        return $this->handleForm(
            $request,
            SaleAddressType::class,
            'ekyna_commerce.checkout.button.edit_invoice',
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
    public function deliveryAddressAction(Request $request): Response
    {
        return $this->handleForm(
            $request,
            SaleAddressType::class,
            'ekyna_commerce.checkout.button.edit_invoice',
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
        string $type,
        string $title,
        string $route,
        array $options = []
    ): Response {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Not yet implemented.');
        }

        $customer = $this->getCustomerOrRedirect();

        $quote = $this->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        if (!$quote->isEditable()) {
            throw new AccessDeniedHttpException('Cart is locked for payment.');
        }

        $form = $this
            ->createForm($type, $quote, array_replace([
                'method' => 'post',
                'action' => $this->generateUrl($route, ['number' => $quote->getNumber()]),
                'attr'   => [
                    'class' => 'form-horizontal',
                ],
            ], $options));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->get('ekyna_commerce.quote.operator')->update($quote);

            if (!$event->hasErrors()) {
                return $this->buildXhrSaleViewResponse($quote);
            }

            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        $modal = $this->createModal($title, $form->createView());

        return $this->get('ekyna_core.modal')->render($modal);
    }

    /**
     * Finds the customer quotes.
     *
     * @param CustomerInterface $customer
     *
     * @return array|QuoteInterface[]
     */
    protected function findQuotesByCustomer(CustomerInterface $customer)
    {
        return $this
            ->get('ekyna_commerce.quote.repository')
            ->findByCustomer($customer, [], true);
    }

    /**
     * Finds the quote by customer and number.
     *
     * @param CustomerInterface $customer
     * @param string            $number
     *
     * @return QuoteInterface
     */
    protected function findQuoteByCustomerAndNumber(CustomerInterface $customer, $number)
    {
        $quote = $this
            ->get('ekyna_commerce.quote.repository')
            ->findOneByCustomerAndNumber($customer, $number);

        if (null === $quote) {
            throw $this->createNotFoundException('Quote not found.');
        }

        return $quote;
    }

    /**
     * Finds the payment by quote and key.
     *
     * @param QuoteInterface $quote
     * @param string         $key
     *
     * @return QuotePaymentInterface
     */
    protected function findPaymentByQuoteAndKey(QuoteInterface $quote, $key)
    {
        $payment = $this
            ->get('ekyna_commerce.quote_payment.repository')
            ->findOneBy([ // TODO repository method
                'quote' => $quote,
                'key'   => $key,
            ]);

        if (null === $payment) {
            throw $this->createNotFoundException('Payment not found.');
        }

        return $payment;
    }

    /**
     * Finds the attachment by quote and id.
     *
     * @param QuoteInterface $quote
     * @param integer        $id
     *
     * @return \Ekyna\Component\Commerce\Common\Model\AttachmentInterface
     */
    protected function findAttachmentByQuoteAndId(QuoteInterface $quote, $id)
    {
        $attachment = $this
            ->get('ekyna_commerce.quote_attachment.repository')
            ->findOneBy([
                'quote' => $quote,
                'id'    => $id,
            ]);

        if (null === $attachment) {
            throw $this->createNotFoundException('Attachment not found.');
        }

        return $attachment;
    }

    /**
     * Builds the recalculate form.
     *
     * @param SaleInterface $sale
     *
     * @return FormInterface
     */
    protected function buildQuantitiesForm(SaleInterface $sale): FormInterface
    {
        return $this->getSaleHelper()->createQuantitiesForm($sale, [
            'method' => 'post',
            'action' => $this->generateUrl('ekyna_commerce_account_quote_recalculate',
                ['number' => $sale->getNumber()]),
        ]);
    }
}
