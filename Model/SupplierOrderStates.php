<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderStates as States;

/**
 * Class SupplierOrderStates
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class SupplierOrderStates extends AbstractConstants
{
    /**
     * {@inheritdoc}
     */
    static public function getConfig()
    {
        $prefix = 'ekyna_commerce.supplier_order.state.';

        return [
            States::STATE_NEW       => [$prefix . States::STATE_NEW,       'default', false],
            States::STATE_ORDERED   => [$prefix . States::STATE_ORDERED,   'primary', true],
            States::STATE_PARTIAL   => [$prefix . States::STATE_PARTIAL,   'warning', false],
            States::STATE_COMPLETED => [$prefix . States::STATE_COMPLETED, 'success', true],
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
