<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Payment;

use Ekyna\Bundle\CommerceBundle\Service\Payment\PaymentHelper;
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
     * @var PaymentHelper
     */
    private $paymentHelper;


    /**
     * Constructor.
     *
     * @param PaymentHelper $paymentHelper
     */
    public function __construct(PaymentHelper $paymentHelper)
    {
        $this->paymentHelper = $paymentHelper;
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

        $this->paymentHelper->status($request);

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
