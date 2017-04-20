<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Payment;

use Ekyna\Component\Commerce\Bridge\Payum\Request\Hang;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class HangController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Payment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class HangController extends AbstractController
{
    public function __invoke(Request $request): Response
    {
        $token = $this->payum->getHttpRequestVerifier()->verify($request);

        $gateway = $this->payum->getGateway($token->getGatewayName());

        $gateway->execute(new Hang($token));

        $this->payum->getHttpRequestVerifier()->invalidate($token);

        return $token->getAfterUrl()
            ? new RedirectResponse($token->getAfterUrl())
            : new Response('', Response::HTTP_NO_CONTENT);
    }
}
