<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Helper\ResourceHelper;
use Ekyna\Component\Commerce\Bridge\Payum\Request\Status;
use Ekyna\Component\Commerce\Payment\Handler\PaymentDoneHandler;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class PaymentController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentController
{
    /**
     * @var PaymentDoneHandler
     */
    private $handler;

    /**
     * @var ResourceHelper
     */
    private $resourceHelper;


    /**
     * Constructor.
     *
     * @param PaymentDoneHandler $handler
     * @param ResourceHelper     $resourceHelper
     */
    public function __construct(PaymentDoneHandler $handler, ResourceHelper $resourceHelper)
    {
        $this->handler = $handler;
        $this->resourceHelper = $resourceHelper;
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

        $path = $this->resourceHelper->generateResourcePath($sale);

        return new RedirectResponse($path);
    }
}
