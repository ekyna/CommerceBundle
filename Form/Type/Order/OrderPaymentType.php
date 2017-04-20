<?php

declare(strict_types=1);

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
    private LockChecker $lockChecker;


    public function __construct(LockChecker $lockChecker)
    {
        $this->lockChecker = $lockChecker;
    }

    protected function isLocked(PaymentInterface $payment): bool
    {
        return $this->lockChecker->isLocked($payment);
    }
}
