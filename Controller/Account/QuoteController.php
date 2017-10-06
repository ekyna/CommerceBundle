<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\SaleStepValidatorInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

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

        return $this->render('EkynaCommerceBundle:Account/Quote:index.html.twig', [
            'quotes' => $quotes,
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
            'quote' => $quote,
            'view'  => $quoteView,
            'quotes' => $quotes,
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

        $quote = $this->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        $cancelUrl = $this->generateUrl('ekyna_commerce_account_quote_show', [
            'number' => $quote->getNumber(),
        ]);

        if (!$this->validateSaleStep($quote, SaleStepValidatorInterface::PAYMENT_STEP)) {
            return $this->redirect($cancelUrl);
        }

        $checkout = $this->get('ekyna_commerce.checkout.payment_manager');

        $checkout->initialize($quote, $this->generateUrl('ekyna_commerce_account_quote_payment', [
            'number' => $quote->getNumber(),
        ]));

        if (null !== $payment = $checkout->handleRequest($request)) {
            $quote->addPayment($payment);

            $event = $this->get('ekyna_commerce.quote.operator')->update($quote);
            if ($event->isPropagationStopped() || $event->hasErrors()) {
                $event->toFlashes($this->getSession()->getFlashBag());

                return $this->redirect($cancelUrl);
            }

            return $this->redirect($this->generateUrl('ekyna_commerce_payment_quote_capture', [
                'key' => $payment->getKey(),
            ]));
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
}
