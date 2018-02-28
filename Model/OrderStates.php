<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Order\Model\OrderStates as States;

/**
 * Class OrderStates
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class OrderStates extends AbstractConstants
{
    /**
     * {@inheritdoc}
     */
    static public function getConfig()
    {
        $prefix = 'ekyna_commerce.order.state.';

        return [
            States::STATE_NEW       => [$prefix . States::STATE_NEW,       'brown',      false],
            States::STATE_PENDING   => [$prefix . States::STATE_PENDING,   'purple',      true],
            States::STATE_REFUSED   => [$prefix . States::STATE_REFUSED,   'red',         false],
            States::STATE_ACCEPTED  => [$prefix . States::STATE_ACCEPTED,  'light-green', true],
            States::STATE_COMPLETED => [$prefix . States::STATE_COMPLETED, 'teal',        true],
            States::STATE_REFUNDED  => [$prefix . States::STATE_REFUNDED,  'indigo',      true],
            States::STATE_CANCELED  => [$prefix . States::STATE_CANCELED,  'default',     false],
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
     * Disabled constructor.
     *
     * @codeCoverageIgnore
     */
    final private function __construct()
    {
    }
}
