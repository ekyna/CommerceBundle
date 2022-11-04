<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account\Quote\Payment;

use Ekyna\Bundle\CommerceBundle\Controller\Account\ControllerInterface;
use Ekyna\Bundle\CommerceBundle\Service\Account\QuoteResourceHelper;
use Ekyna\Bundle\CommerceBundle\Service\Payment\PaymentHelper;
use Ekyna\Bundle\UiBundle\Form\Type\ConfirmType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

use function Symfony\Component\Translation\t;

/**
 * Class CancelController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account\Quote\Payment
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CancelController implements ControllerInterface
{
    public function __construct(
        private readonly QuoteResourceHelper   $resourceHelper,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly PaymentHelper         $paymentHelper,
        private readonly FormFactoryInterface  $formFactory,
        private readonly Environment           $twig,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $customer = $this->resourceHelper->getCustomer();

        $quote = $this->resourceHelper->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        $payment = $this->resourceHelper->findPaymentByQuoteAndKey($quote, $request->attributes->get('key'));

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

        $quotes = $this->resourceHelper->findQuotesByCustomer($customer);

        $content = $this->twig->render('@EkynaCommerce/Account/Quote/payment_cancel.html.twig', [
            'customer' => $customer,
            'quote'    => $quote,
            'form'     => $form->createView(),
            'quotes'   => $quotes,
        ]);

        return (new Response($content))->setPrivate();
    }
}
