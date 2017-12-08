<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Form\Type\Quote\QuoteAttachmentType;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CoreBundle\Form\Type\ConfirmType;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\SaleStepValidatorInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Payment\Model\PaymentTransitions;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Quote\Model\QuotePaymentInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteStates;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class QuoteController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteController extends AbstractController
{
    /**
     * Quote index action.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $customer = $this->getCustomerOrRedirect();

        $quotes = $this
            ->get('ekyna_commerce.quote.repository')
            ->findByCustomer($customer);

        $childrenQuotes = null;
        if ($customer->hasChildren()) {
            $childrenQuotes = $this
                ->get('ekyna_commerce.quote.repository')
                ->findByParent($customer);
        }

        return $this->render('EkynaCommerceBundle:Account/Quote:index.html.twig', [
            'quotes'         => $quotes,
            'childrenQuotes' => $childrenQuotes,
        ]);
    }

    /**
     * Quote show action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Request $request)
    {
        $customer = $this->getCustomerOrRedirect();

        $quote = $this->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        $quoteView = $this->get('ekyna_commerce.common.view_builder')->buildSaleView($quote, [
            'taxes_view' => false,
        ]);

        $quotes = $this
            ->get('ekyna_commerce.quote.repository')
            ->findByCustomer($customer);

        return $this->render('EkynaCommerceBundle:Account/Quote:show.html.twig', [
            'customer'     => $customer,
            'quote'        => $quote,
            'view'         => $quoteView,
            'quotes'       => $quotes,
            'route_prefix' => 'ekyna_commerce_account_quote',
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

        $checkout = $this->get('ekyna_commerce.checkout.payment_manager');

        $checkout->initialize($quote, $this->generateUrl('ekyna_commerce_account_quote_payment_create', [
            'number' => $quote->getNumber(),
        ]));

        /** @var QuotePaymentInterface $payment */
        if (null !== $payment = $checkout->handleRequest($request)) {
            $quote->addPayment($payment);

            $event = $this->get('ekyna_commerce.quote.operator')->update($quote);
            if ($event->isPropagationStopped() || $event->hasErrors()) {
                $event->toFlashes($this->getSession()->getFlashBag());

                return $this->redirect($cancelUrl);
            }

            return $this
                ->get('ekyna_commerce.payment_helper')
                ->capture($payment, $this->getPaymentStatusUrl($quote, $payment));
        }

        $quotes = $this
            ->get('ekyna_commerce.quote.repository')
            ->findByCustomer($customer);

        return $this->render('EkynaCommerceBundle:Account/Quote:payment.html.twig', [
            'quote'  => $quote,
            'forms'  => $checkout->getFormsViews(),
            'quotes' => $quotes,
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
            'message'     => 'ekyna_commerce.account.payment.confirm_cancel',
            'cancel_path' => $cancelUrl,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this
                ->get('ekyna_commerce.payment_helper')
                ->cancel($payment, $this->getPaymentStatusUrl($quote, $payment));
        }

        $quotes = $this
            ->get('ekyna_commerce.quote.repository')
            ->findByCustomer($customer);

        return $this->render('EkynaCommerceBundle:Account/Quote:payment_cancel.html.twig', [
            'quote'  => $quote,
            'form'   => $form->createView(),
            'quotes' => $quotes,
        ]);
    }

    /**
     * Payment status action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function paymentStatusAction(Request $request)
    {
        $payment = $this->get('ekyna_commerce.payment_helper')->status($request);

        if (!$payment instanceof QuotePaymentInterface) {
            throw new InvalidArgumentException("Expected instance of " . QuotePaymentInterface::class);
        }

        /** @var QuoteInterface $quote */
        $quote = $payment->getQuote();

        // TODO Check that quote belongs to the current user
        // Problem : token has been invalidated

        // If quote is ACCEPTED
        if ($quote->getState() === QuoteStates::STATE_ACCEPTED) {
            // Transform to order
            $transformer = $this->get('ekyna_commerce.sale_transformer');

            // New order
            /** @var \Ekyna\Bundle\CommerceBundle\Model\OrderInterface $order */
            $order = $this->get('ekyna_commerce.order.repository')->createNew();

            // Initialize transformation
            $transformer->initialize($quote, $order);
            // Transform
            if (null === $event = $transformer->transform()) {

                // Redirect to order details
                return $this->redirect($this->generateUrl('ekyna_commerce_account_order_show', [
                    'number' => $order->getNumber(),
                ]));
            } else {
                $event->toFlashes($this->getFlashBag());
            }
        }

        // Redirect to quote details
        return $this->redirectToRoute('ekyna_commerce_account_quote_show', [
            'number' => $quote->getNumber(),
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

        $cancelPath = $this->generateUrl('ekyna_commerce_account_quote_show', [
            'number' => $quote->getNumber(),
        ]);

        $form = $this->createForm(QuoteAttachmentType::class, $attachment, [
            'action' => $this->generateUrl('ekyna_commerce_account_quote_attachment_create', [
                'number' => $quote->getNumber(),
            ]),
        ]);

        $this->createFormFooter($form, ['cancel_path' => $cancelPath]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->get('ekyna_commerce.quote_attachment.operator')->create($attachment);

            $event->toFlashes($this->getFlashBag());

            if (!$event->hasErrors()) {
                return $this->redirect($cancelPath);
            }
        }

        $quotes = $this
            ->get('ekyna_commerce.quote.repository')
            ->findByCustomer($customer);

        return $this->render('EkynaCommerceBundle:Account/Quote:attachment_create.html.twig', [
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
     * Returns the payment status url.
     *
     * @param QuoteInterface        $quote
     * @param QuotePaymentInterface $payment
     *
     * @return string
     */
    protected function getPaymentStatusUrl(QuoteInterface $quote, QuotePaymentInterface $payment)
    {
        return $this->generateUrl('ekyna_commerce_account_quote_payment_status', [
            'number' => $quote->getNumber(),
            'key'    => $payment->getKey(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
