<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Payment;

use Ekyna\Component\Commerce\Bridge\Payum\Request\Hang;
use Payum\Bundle\PayumBundle\Controller\PayumController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class HangController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Payment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class HangController extends PayumController
{
    public function hang(Request $request)
    {
        $token = $this->getPayum()->getHttpRequestVerifier()->verify($request);

        $gateway = $this->getPayum()->getGateway($token->getGatewayName());

        $gateway->execute(new Hang($token));

        $this->getPayum()->getHttpRequestVerifier()->invalidate($token);

        return $token->getAfterUrl()
            ? $this->redirect($token->getAfterUrl())
            : new Response('', Response::HTTP_NO_CONTENT);
    }
}
