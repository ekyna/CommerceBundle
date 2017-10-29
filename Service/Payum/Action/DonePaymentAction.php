<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Payum\Action;

use Ekyna\Bundle\CommerceBundle\Service\Payum\Request\Done;
use Ekyna\Bundle\CommerceBundle\Service\Payum\Request\GetStatus;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Sync;

/**
 * Class DonePaymentAction
 * @package Ekyna\Bundle\CommerceBundle\Service\Payum\Action
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class DonePaymentAction extends AbstractAction
{
    /**
     * {@inheritDoc}
     *
     * @param Done $request
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
        return $request instanceof Done
            && $request->getToken()
            && $request->getModel() instanceof PaymentInterface;
    }
}
