<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectStates as States;

/**
 * Class StockStates
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class StockSubjectStates extends AbstractConstants
{
    /**
     * @inheritdoc
     */
    static public function getConfig()
    {
        $prefix = 'ekyna_commerce.stock_subject.state.';

        return [
            States::STATE_IN_STOCK     => [$prefix . States::STATE_IN_STOCK,     'success'],
            States::STATE_PRE_ORDER    => [$prefix . States::STATE_PRE_ORDER,    'warning'],
            States::STATE_OUT_OF_STOCK => [$prefix . States::STATE_OUT_OF_STOCK, 'danger'],
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
