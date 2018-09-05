<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Customer\Model\CustomerStates as States;

/**
 * Class CustomerStates
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class CustomerStates extends AbstractConstants
{
    /**
     * {@inheritdoc}
     */
    static public function getConfig()
    {
        $prefix = 'ekyna_commerce.customer.state.';

        return [
            States::STATE_NEW       => [$prefix . States::STATE_NEW,       'brown'],
            States::STATE_VALID     => [$prefix . States::STATE_VALID,     'light-green'],
            States::STATE_FRAUDSTER => [$prefix . States::STATE_FRAUDSTER, 'red'],
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
