<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Payum\Action\Offline;

use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Offline\Constants;

/**
 * Class CaptureAction
 * @package Ekyna\Bundle\CommerceBundle\Service\Payum\Action\Offline
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CaptureAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $payment = $request->getModel();

        $details = $payment->getDetails();

        if (array_key_exists(Constants::FIELD_PAID, $details)) {
            return;
        }

        $details[Constants::FIELD_PAID] = false;

        $payment->setDetails($details);

        return;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof Capture
            && $request->getModel() instanceof PaymentInterface;
    }
}
