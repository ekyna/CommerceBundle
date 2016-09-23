<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\CoreBundle\Model\AbstractConstants;
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
            States::STATE_NEW       => [$prefix . States::STATE_NEW,       'default', false],
            States::STATE_COMPLETED => [$prefix . States::STATE_COMPLETED, 'success', true],
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

    /**
     * Returns the notifiable states.
     *
     * @return array
     */
    static public function getNotifiableStates()
    {
        $states = [];
        foreach (static::getConfig() as $state => $config) {
            if ($config[2]) {
                $states[] = $state;
            }
        }
        return $states;
    }
}
