<?php

declare(strict_types=1);

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
    private PaymentHelper $paymentHelper;

    public function __construct(PaymentHelper $paymentHelper)
    {
        $this->paymentHelper = $paymentHelper;
    }

    public function __invoke(Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('XHR is not supported.');
        }

        $this->paymentHelper->notify($request);

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
