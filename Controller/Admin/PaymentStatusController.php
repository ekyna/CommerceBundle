<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\CommerceBundle\Service\Payment\PaymentHelper;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PaymentStatusController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentStatusController
{
    private PaymentHelper $paymentHelper;
    private ResourceHelper $resourceHelper;


    public function __construct(PaymentHelper $paymentHelper, ResourceHelper $resourceHelper)
    {
        $this->paymentHelper = $paymentHelper;
        $this->resourceHelper = $resourceHelper;
    }

    public function __invoke(Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {
            return new Response('XHR is not supported.', Response::HTTP_NOT_FOUND);
        }

        if (null === $payment = $this->paymentHelper->status($request)) {
            // Sale has been deleted (fraud)
            return new RedirectResponse(
                $this->resourceHelper->getUrlGenerator()->generate('admin_dashboard')
            );
        }

        $sale = $payment->getSale();

        return new RedirectResponse($this->resourceHelper->generateResourcePath($sale, ReadAction::class));
    }
}
