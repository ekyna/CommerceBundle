<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Service\Payment\PaymentHelper;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class PaymentStatusController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentStatusController
{
    public function __construct(
        private readonly PaymentHelper         $paymentHelper,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        if ($request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('XHR is not supported.');
        }

        if (null === $payment = $this->paymentHelper->status($request)) {
            // Sale has been deleted (fraud)
            $route = 'ekyna_commerce_account_index';
            $parameters = [];
        } else {
            $sale = $payment->getSale();

            if ($sale instanceof QuoteInterface) {
                $route = 'ekyna_commerce_account_quote_read';
                $parameters = [
                    'number' => $sale->getNumber(),
                ];
            } elseif ($sale instanceof OrderInterface) {
                $route = 'ekyna_commerce_account_order_read';
                $parameters = [
                    'number' => $sale->getNumber(),
                ];
            } else {
                throw new RuntimeException('Unexpected payment.');
            }
        }

        $path = $this->urlGenerator->generate($route, $parameters);

        return new RedirectResponse($path);
    }
}