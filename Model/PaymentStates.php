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
    static public function getConfig(): array
    {
        $prefix = 'ekyna_commerce.payment.state.';
        $suffix = '.label';

        return [
            States::STATE_NEW         => [$prefix.States::STATE_NEW.$suffix,         'brown'],
            States::STATE_PENDING     => [$prefix.States::STATE_PENDING.$suffix,     'orange'],
            States::STATE_CAPTURED    => [$prefix.States::STATE_CAPTURED.$suffix,    'light-green'],
            States::STATE_CANCELED    => [$prefix.States::STATE_CANCELED.$suffix,    'default'],
            States::STATE_FAILED      => [$prefix.States::STATE_FAILED.$suffix,      'red'],
            States::STATE_REFUNDED    => [$prefix.States::STATE_REFUNDED.$suffix,    'indigo'],
            States::STATE_AUTHORIZED  => [$prefix.States::STATE_AUTHORIZED.$suffix,  'light-green'],
            States::STATE_SUSPENDED   => [$prefix.States::STATE_SUSPENDED.$suffix,   'orange'],
            States::STATE_EXPIRED     => [$prefix.States::STATE_EXPIRED.$suffix,     'red'],
            States::STATE_UNKNOWN     => [$prefix.States::STATE_UNKNOWN.$suffix,     'default'],
            // Sale only
            States::STATE_OUTSTANDING => [$prefix.States::STATE_OUTSTANDING.$suffix, 'pink'],
            States::STATE_DEPOSIT     => [$prefix.States::STATE_DEPOSIT.$suffix,     'purple'],
            States::STATE_COMPLETED   => [$prefix.States::STATE_COMPLETED.$suffix,   'teal'],
        ];
    }
}
