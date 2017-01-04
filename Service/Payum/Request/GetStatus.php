<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Payum\Request;

use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Payum\Core\Request\BaseGetStatus;

/**
 * Class GetStatus
 * @package Ekyna\Bundle\CommerceBundle\Service\Payum\Request
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class GetStatus extends BaseGetStatus
{
    /**
     * {@inheritdoc}
     */
    public function markNew()
    {
        $this->status = PaymentStates::STATE_NEW;
    }

    /**
     * {@inheritdoc}
     */
    public function isNew()
    {
        return $this->status === PaymentStates::STATE_NEW;
    }

    /**
     * {@inheritdoc}
     */
    public function markPending()
    {
        $this->status = PaymentStates::STATE_PENDING;
    }

    /**
     * {@inheritdoc}
     */
    public function isPending()
    {
        return $this->status === PaymentStates::STATE_PENDING;
    }

    /**
     * {@inheritdoc}
     */
    public function markSuspended()
    {
        $this->status = PaymentStates::STATE_SUSPENDED;
    }

    /**
     * {@inheritdoc}
     */
    public function isSuspended()
    {
        return $this->status === PaymentStates::STATE_SUSPENDED;
    }

    /**
     * {@inheritdoc}
     */
    public function markExpired()
    {
        $this->status = PaymentStates::STATE_EXPIRED;
    }

    /**
     * {@inheritdoc}
     */
    public function isExpired()
    {
        return $this->status === PaymentStates::STATE_EXPIRED;
    }

    /**
     * {@inheritdoc}
     */
    public function markCanceled()
    {
        $this->status = PaymentStates::STATE_CANCELLED;
    }

    /**
     * {@inheritdoc}
     */
    public function isCanceled()
    {
        return $this->status === PaymentStates::STATE_CANCELLED;
    }

    /**
     * {@inheritdoc}
     */
    public function markFailed()
    {
        $this->status = PaymentStates::STATE_FAILED;
    }

    /**
     * {@inheritdoc}
     */
    public function isFailed()
    {
        return $this->status === PaymentStates::STATE_FAILED;
    }

    /**
     * {@inheritdoc}
     */
    public function markCaptured()
    {
        $this->status = PaymentStates::STATE_CAPTURED;
    }

    /**
     * {@inheritdoc}
     */
    public function isCaptured()
    {
        return $this->status === PaymentStates::STATE_CAPTURED;
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthorized()
    {
        return $this->status === PaymentStates::STATE_AUTHORIZED;
    }

    /**
     * {@inheritdoc}
     */
    public function markAuthorized()
    {
        $this->status = PaymentStates::STATE_AUTHORIZED;
    }

    /**
     * @inheritDoc
     */
    public function isPayedout()
    {
        // TODO: Implement isPayedout() method.
    }

    /**
     * @inheritDoc
     */
    public function markPayedout()
    {
        // TODO: Implement markPayedout() method.
    }

    /**
     * {@inheritdoc}
     */
    public function isRefunded()
    {
        return $this->status === PaymentStates::STATE_REFUNDED;
    }

    /**
     * {@inheritdoc}
     */
    public function markRefunded()
    {
        $this->status = PaymentStates::STATE_REFUNDED;
    }

    /**
     * {@inheritdoc}
     */
    public function markUnknown()
    {
        $this->status = PaymentStates::STATE_UNKNOWN;
    }

    /**
     * {@inheritdoc}
     */
    public function isUnknown()
    {
        return $this->status === PaymentStates::STATE_UNKNOWN;
    }
}
