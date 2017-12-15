<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Component\Commerce\Bridge\Payum\Request\Status;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Payment\Handler\PaymentDoneHandler;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class PaymentController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentController
{
    /**
     * @var PaymentDoneHandler
     */
    private $handler;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;


    /**
     * Constructor.
     *
     * @param PaymentDoneHandler    $handler
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(PaymentDoneHandler $handler, UrlGeneratorInterface $urlGenerator)
    {
        $this->handler = $handler;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Payment status action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function statusAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            throw new NotFoundHttpException("XHR is not supported.");
        }

        $payum = $this->handler->getPayum();

        $token = $payum->getHttpRequestVerifier()->verify($request);

        $gateway = $payum->getGateway($token->getGatewayName());

        $gateway->execute($done = new Status($token));

        $payum->getHttpRequestVerifier()->invalidate($token);

        /** @var PaymentInterface $payment */
        $payment = $done->getFirstModel();

        $sale = $this->handler->handle($payment);

        if ($sale instanceof QuoteInterface) {
            $route = 'ekyna_commerce_account_quote_show';
            $parameters = [
                'number' => $sale->getNumber(),
            ];
        } elseif ($sale instanceof OrderInterface) {
            $route = 'ekyna_commerce_account_order_show';
            $parameters = [
                'number' => $sale->getNumber(),
            ];
        } else {
            throw new RuntimeException("Unexpected payment.");
        }

        $path = $this->urlGenerator->generate($route, $parameters);

        return new RedirectResponse($path);
    }
}
