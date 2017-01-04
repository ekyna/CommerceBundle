<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Payum\Action;

use Ekyna\Bundle\CommerceBundle\Model\PaymentStates;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;

/**
 * Class AbstractPaymentStateAwareAction
 * @package Ekyna\Bundle\CommerceBundle\Service\Payum\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractPaymentStateAwareAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * Updates the payment state.
     *
     * @param PaymentInterface $payment
     * @param string           $nextState
     */
    protected function updatePaymentState(PaymentInterface $payment, $nextState)
    {
        PaymentStates::isValid($nextState, true);

        $payment->setState($nextState);

        // TODO use a state machine.
    }
}
