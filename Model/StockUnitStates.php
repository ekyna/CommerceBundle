<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates as States;

/**
 * Class StockUnitStates
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class StockUnitStates extends AbstractConstants
{
    /**
     * @inheritdoc
     */
    static public function getConfig()
    {
        $prefix = 'ekyna_commerce.stock_unit.state.';

        return [
            States::STATE_NEW     => [$prefix . States::STATE_NEW,     'primary', false],
            States::STATE_PENDING => [$prefix . States::STATE_PENDING, 'warning', false],
            States::STATE_READY   => [$prefix . States::STATE_READY,   'success', false],
            States::STATE_CLOSED  => [$prefix . States::STATE_CLOSED,  'default', false],
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
