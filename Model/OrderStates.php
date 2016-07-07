<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\CoreBundle\Model\AbstractConstants;
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
            States::STATE_NEW       => [$prefix . States::STATE_NEW,       'default', false],
            States::STATE_PENDING   => [$prefix . States::STATE_PENDING,   'warning', true],
            States::STATE_REFUSED   => [$prefix . States::STATE_REFUSED,   'danger',  false],
            States::STATE_ACCEPTED  => [$prefix . States::STATE_ACCEPTED,  'success', true],
            States::STATE_COMPLETED => [$prefix . States::STATE_COMPLETED, 'success', true],
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
