<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account\Quote\Payment;

use Ekyna\Bundle\CommerceBundle\Service\Account\QuoteResourceHelper;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Bundle\CommerceBundle\Service\Payment\CheckoutManager;
use Ekyna\Bundle\CommerceBundle\Service\Payment\PaymentHelper;
use Ekyna\Bundle\UiBundle\Service\FlashHelper;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\SaleStepValidatorInterface;
use Ekyna\Component\Commerce\Quote\Model\QuotePaymentInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

use function Symfony\Component\Translation\t;

/**
 * Class CreateController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account\Quote\Payment
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CreateController
{
    public function __construct(
        private readonly QuoteResourceHelper        $resourceHelper,
        private readonly UrlGeneratorInterface      $urlGenerator,
        private readonly ConstantsHelper            $constantsHelper,
        private readonly FlashHelper                $flashHelper,
        private readonly SaleStepValidatorInterface $stepValidator,
        private readonly CheckoutManager            $checkoutManager,
        private readonly ResourceManagerInterface   $quoteManager,
        private readonly PaymentHelper              $paymentHelper,
        private readonly Environment                $twig,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $customer = $this->resourceHelper->getCustomer();

        $quote = $this->resourceHelper->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        $redirect = $this->urlGenerator->generate('ekyna_commerce_account_quote_read', [
            'number' => $quote->getNumber(),
        ]);

        if ($customer->hasParent()) {
            $message = t('account.quote.message.payment_denied', [
                '{identity}' => $this->constantsHelper->renderIdentity($customer->getParent()),
            ], 'EkynaCommerce');

            $this->flashHelper->addFlash($message, 'warning');

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

            $event = $this->quoteManager->update($quote);
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

        $quotes = $this->resourceHelper->findQuotesByCustomer($customer);

        $content = $this->twig->render('@EkynaCommerce/Account/Quote/payment_create.html.twig', [
            'customer' => $customer,
            'quote'    => $quote,
            'forms'    => $this->checkoutManager->getFormsViews(),
            'quotes'   => $quotes,
        ]);

        return (new Response($content))->setPrivate();
    }
}
