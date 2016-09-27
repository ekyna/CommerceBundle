<?php

namespace Ekyna\Bundle\CommerceBundle\Event;

use Ekyna\Bundle\AdminBundle\Event\ResourceEvent;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;

/**
 * Class OrderPaymentEvent
 * @package Ekyna\Bundle\CommerceBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentEvent extends ResourceEvent
{
    /**
     * Constructor.
     *
     * @param PaymentInterface $payment
     */
    public function __construct(PaymentInterface $payment)
    {
        $this->setResource($payment);
    }
}
