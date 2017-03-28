<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Cart\Model\CartStates as States;

/**
 * Class CartStates
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class CartStates extends AbstractConstants
{
    /**
     * {@inheritdoc}
     */
    static public function getConfig()
    {
        $prefix = 'ekyna_commerce.cart.state.';

        return [
            States::STATE_NEW      => [$prefix . States::STATE_NEW,       'default', false],
            States::STATE_ACCEPTED => [$prefix . States::STATE_ACCEPTED, 'success', true],
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
