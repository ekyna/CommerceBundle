<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;

/**
 * Class PaymentTransitions
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentTransitions extends AbstractConstants
{
    const TRANSITION_CANCEL = 'cancel';
    const TRANSITION_HANG   = 'hang';
    const TRANSITION_ACCEPT = 'accept';
    const TRANSITION_REJECT = 'reject';
    const TRANSITION_REFUND = 'refund';


    /**
     * {@inheritdoc}
     */
    static public function getConfig()
    {
        $prefix = 'ekyna_commerce.payment.transition.';
        $suffix = '.label';

        return [
            static::TRANSITION_CANCEL => [$prefix . static::TRANSITION_CANCEL . $suffix, 'warning'],
            static::TRANSITION_HANG   => [$prefix . static::TRANSITION_HANG .   $suffix, 'warning'],
            static::TRANSITION_ACCEPT => [$prefix . static::TRANSITION_ACCEPT . $suffix, 'success'],
            static::TRANSITION_REJECT => [$prefix . static::TRANSITION_REJECT . $suffix, 'danger'],
            static::TRANSITION_REFUND => [$prefix . static::TRANSITION_REFUND . $suffix, 'primary'],
        ];
    }

    /**
     * Returns the confirmation message for the given transition.
     *
     * @param string $transition
     *
     * @return string
     */
    public static function getConfirm($transition)
    {
        static::isValid($transition, true);

        return sprintf('ekyna_commerce.payment.transition.%s.confirm', $transition);
    }

    /**
     * Returns the theme for the given transition.
     *
     * @param string $transition
     *
     * @return string
     */
    static public function getTheme($transition)
    {
        static::isValid($transition, true);

        return static::getConfig()[$transition][1];
    }

    /**
     * Returns the available payment transitions.
     *
     * @param PaymentInterface $payment
     * @param bool             $admin
     *
     * @return array
     */
    static function getAvailableTransitions(PaymentInterface $payment, $admin = false)
    {
        $transitions = [];

        /** @var PaymentMethodInterface $method */
        $method = $payment->getMethod();
        $state = $payment->getState();

        if ($admin) {
            if ($method->isManual()) {
                switch ($state) {
                    case PaymentStates::STATE_PENDING:
                        $transitions[] = static::TRANSITION_CANCEL;
                        $transitions[] = static::TRANSITION_ACCEPT;
                        break;
                    case PaymentStates::STATE_CAPTURED:
                        $transitions[] = static::TRANSITION_CANCEL;
                        $transitions[] = static::TRANSITION_HANG;
                        $transitions[] = static::TRANSITION_REFUND;
                        break;
                    case PaymentStates::STATE_REFUNDED:
                        $transitions[] = static::TRANSITION_CANCEL;
                        $transitions[] = static::TRANSITION_HANG;
                        $transitions[] = static::TRANSITION_ACCEPT;
                        break;
                    case PaymentStates::STATE_CANCELED:
                        $transitions[] = static::TRANSITION_HANG;
                        $transitions[] = static::TRANSITION_ACCEPT;
                        break;
                }
            } else {
                if ($state === PaymentStates::STATE_CAPTURED) {
                    $transitions[] = static::TRANSITION_REFUND;
                }
                /*if ($state === PaymentStates::STATE_PENDING) {
                    $d = $payment->getUpdatedAt()->diff(new \DateTime());
                    if ($d->y || $d->m || $d->d) {
                        $transitions[] = static::TRANSITION_CANCEL;
                    }
                }*/
            }
        } else {
            if ($state === PaymentStates::STATE_PENDING && $method->isManual()) {
                $transitions[] = static::TRANSITION_CANCEL;
            }
        }

        return $transitions;
    }
}
