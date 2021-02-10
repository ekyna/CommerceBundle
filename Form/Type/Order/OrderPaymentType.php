<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Order;

use Ekyna\Bundle\CommerceBundle\Form\Type\Payment\PaymentType;
use Ekyna\Component\Commerce\Common\Locking\LockChecker;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;

/**
 * Class OrderPaymentType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Order
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderPaymentType extends PaymentType
{
    /**
     * @var LockChecker
     */
    private $lockChecker;


    /**
     * Constructor.
     *
     * @param LockChecker $lockChecker
     * @param string $dataClass
     */
    public function __construct(LockChecker $lockChecker, string $dataClass)
    {
        parent::__construct($dataClass);

        $this->lockChecker = $lockChecker;
    }

    /**
     * @inheritDoc
     */
    protected function isLocked(PaymentInterface $payment): bool
    {
        return $this->lockChecker->isLocked($payment);
    }
}
