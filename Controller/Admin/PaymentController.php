<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Helper\ResourceHelper;
use Ekyna\Bundle\CommerceBundle\Service\Payment\PaymentHelper;
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
     * @var PaymentHelper
     */
    private $paymentHelper;

    /**
     * @var ResourceHelper
     */
    private $resourceHelper;


    /**
     * Constructor.
     *
     * @param PaymentHelper  $paymentHelper
     * @param ResourceHelper $resourceHelper
     */
    public function __construct(PaymentHelper $paymentHelper, ResourceHelper $resourceHelper)
    {
        $this->paymentHelper = $paymentHelper;
        $this->resourceHelper = $resourceHelper;
    }

    /**
     * Payment status action.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function status(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            throw new NotFoundHttpException("XHR is not supported.");
        }

        if (null === $payment = $this->paymentHelper->status($request)) {
            // Sale has been deleted (fraud)
            return new RedirectResponse(
                $this->resourceHelper->getUrlGenerator()->generate('ekyna_admin_dashboard')
            );
        }

        $sale = $payment->getSale();

        return new RedirectResponse($this->resourceHelper->generateResourcePath($sale));
    }
}
