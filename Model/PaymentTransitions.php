<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Payment\Model\PaymentTransitions as Transitions;

/**
 * Class PaymentTransitions
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class PaymentTransitions extends AbstractConstants
{
    /**
     * @inheritDoc
     */
    static public function getConfig(): array
    {
        $prefix = 'ekyna_commerce.payment.transition.';
        $suffix = '.label';

        return [
            Transitions::TRANSITION_CANCEL    => [$prefix . Transitions::TRANSITION_CANCEL . $suffix,    'grey'],
            Transitions::TRANSITION_HANG      => [$prefix . Transitions::TRANSITION_HANG . $suffix,      'orange'],
            Transitions::TRANSITION_AUTHORIZE => [$prefix . Transitions::TRANSITION_AUTHORIZE . $suffix, 'light-green'],
            Transitions::TRANSITION_ACCEPT    => [$prefix . Transitions::TRANSITION_ACCEPT . $suffix,    'green'],
            Transitions::TRANSITION_PAYOUT    => [$prefix . Transitions::TRANSITION_PAYOUT . $suffix,    'teal'],
            Transitions::TRANSITION_REJECT    => [$prefix . Transitions::TRANSITION_REJECT . $suffix,    'red'],
            Transitions::TRANSITION_REFUND    => [$prefix . Transitions::TRANSITION_REFUND . $suffix,    'indigo'],
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
}
