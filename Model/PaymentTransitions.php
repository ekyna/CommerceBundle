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
     * {@inheritdoc}
     */
    static public function getConfig()
    {
        $prefix = 'ekyna_commerce.payment.transition.';
        $suffix = '.label';

        return [
            Transitions::TRANSITION_CANCEL => [$prefix . Transitions::TRANSITION_CANCEL . $suffix, 'warning'],
            Transitions::TRANSITION_HANG   => [$prefix . Transitions::TRANSITION_HANG .   $suffix, 'warning'],
            Transitions::TRANSITION_ACCEPT => [$prefix . Transitions::TRANSITION_ACCEPT . $suffix, 'success'],
            Transitions::TRANSITION_REJECT => [$prefix . Transitions::TRANSITION_REJECT . $suffix, 'danger'],
            Transitions::TRANSITION_REFUND => [$prefix . Transitions::TRANSITION_REFUND . $suffix, 'primary'],
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
     * Disabled constructor.
     *
     * @codeCoverageIgnore
     */
    final private function __construct()
    {
    }
}
