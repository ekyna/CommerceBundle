<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Payment;

use Ekyna\Bundle\CommerceBundle\Entity\PaymentSecurityToken;
use Ekyna\Component\Commerce\Payment\Handler\PaymentDoneHandler;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Payum\Core\Request\Notify;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class NotifyController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Payment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotifyController
{
    /**
     * @var PaymentDoneHandler
     */
    private $handler;

    /**
     * @var bool
     */
    private $debug;


    /**
     * Constructor.
     *
     * @param PaymentDoneHandler $handler
     * @param bool               $debug
     */
    public function __construct(PaymentDoneHandler $handler, $debug = false)
    {
        $this->handler = $handler;
        $this->debug = $debug;
    }

    /**
     * Payment notify action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function doAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            throw new NotFoundHttpException("XHR is not supported.");
        }

        $payum = $this->handler->getPayum();

        /** @var PaymentSecurityToken $token */
        $token = $payum->getHttpRequestVerifier()->verify($request);

        $gateway = $payum->getGateway($token->getGatewayName());

        $gateway->execute($notify = new Notify($token));

        // Invalidate token
        if (!$this->debug) {
            $payum->getHttpRequestVerifier()->invalidate($token);
        }

        /** @var PaymentInterface $payment */
        $payment = $notify->getFirstModel();

        // Handle done payment
        $this->handler->handle($payment);

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
