<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Support\Model\TicketStates as States;

/**
 * Class TicketStates
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class TicketStates extends AbstractConstants
{
    /**
     * {@inheritdoc}
     */
    static public function getConfig(): array
    {
        $prefix = 'ekyna_commerce.status.';

        return [                                                        // User       Admin
            States::STATE_NEW      => [$prefix . States::STATE_NEW,      'brown',   'brown'],
            States::STATE_OPENED   => [$prefix . States::STATE_OPENED,   'success', 'warning'], // Waiting for admin reply
            States::STATE_PENDING  => [$prefix . States::STATE_PENDING,  'warning', 'success'], // Waiting for customer reply
            States::STATE_INTERNAL => [$prefix . States::STATE_INTERNAL, 'purple',  'purple'],
            States::STATE_CLOSED   => [$prefix . States::STATE_CLOSED,   'default', 'default'],
        ];
    }

    /**
     * Returns the theme for the given state.
     *
     * @param string $state
     * @param bool   $admin
     *
     * @return string
     */
    static public function getTheme(string $state, bool $admin = false): ?string
    {
        static::isValid($state, true);

        return static::getConfig()[$state][$admin ? 2 : 1];
    }
}
