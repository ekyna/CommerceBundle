<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates as States;

/**
 * Class PaymentStates
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class PaymentStates extends AbstractConstants
{
    /**
     * {@inheritdoc}
     */
    static public function getConfig()
    {
        $prefix = 'ekyna_commerce.payment.state.';
        $suffix = '.label';

        return [
            States::STATE_NEW        => [$prefix.States::STATE_NEW.$suffix,        'default'],
            States::STATE_PENDING    => [$prefix.States::STATE_PENDING.$suffix,    'warning'],
            States::STATE_CAPTURED   => [$prefix.States::STATE_CAPTURED.$suffix,   'success'],
            States::STATE_CANCELLED  => [$prefix.States::STATE_CANCELLED.$suffix,  'default'],
            States::STATE_FAILED     => [$prefix.States::STATE_FAILED.$suffix,     'danger'],
            States::STATE_REFUNDED   => [$prefix.States::STATE_REFUNDED.$suffix,   'primary'],
            States::STATE_AUTHORIZED => [$prefix.States::STATE_AUTHORIZED.$suffix, 'success'],
            States::STATE_SUSPENDED  => [$prefix.States::STATE_SUSPENDED.$suffix,  'warning'],
            States::STATE_EXPIRED    => [$prefix.States::STATE_EXPIRED.$suffix,    'danger'],
            States::STATE_UNKNOWN    => [$prefix.States::STATE_UNKNOWN.$suffix,    'default'],
        ];
    }

    /**
     * Returns the theme for the given state.
     *
     * @param string $state
     * @return string
     */
    static public function getTheme($state)
    {
        static::isValid($state, true);

        return static::getConfig()[$state][1];
    }
}
