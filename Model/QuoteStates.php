<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Quote\Model\QuoteStates as States;

/**
 * Class QuoteStates
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class QuoteStates extends AbstractConstants
{
    /**
     * {@inheritdoc}
     */
    static public function getConfig()
    {
        $prefix = 'ekyna_commerce.quote.state.';

        return [
            States::STATE_NEW       => [$prefix . States::STATE_NEW,       'default', false],
            States::STATE_PENDING   => [$prefix . States::STATE_PENDING,   'warning', true],
            States::STATE_REFUSED   => [$prefix . States::STATE_REFUSED,   'danger',  false],
            States::STATE_ACCEPTED  => [$prefix . States::STATE_ACCEPTED,  'success', true],
            States::STATE_REFUNDED  => [$prefix . States::STATE_REFUNDED,  'primary', true],
            States::STATE_CANCELLED => [$prefix . States::STATE_CANCELLED, 'default', false],
        ];
    }

    /**
     * Returns the theme for the given state.
     *
     * @param string $state
     *
     * @return string
     */
    static public function getTheme($state)
    {
        static::isValid($state, true);

        return static::getConfig()[$state][1];
    }
}
