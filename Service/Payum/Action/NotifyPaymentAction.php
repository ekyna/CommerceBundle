<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Payum\Action;

use Ekyna\Bundle\CommerceBundle\Service\Payum\Request\GetStatus;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Sync;

/**
 * Class NotifyPaymentAction
 * @package Ekyna\Bundle\CommerceBundle\Service\Payum\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class NotifyPaymentAction extends AbstractAction
{
    /**
     * {@inheritDoc}
     *
     * @param Notify $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getModel();

        $this->gateway->execute(new Sync($payment));

        $status = new GetStatus($payment);
        $this->gateway->execute($status);

        $nextState = $status->getValue();

        $this->updatePaymentState($payment, $nextState);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof Notify
            && $request->getToken()
            && $request->getModel() instanceof PaymentInterface;
    }
}
