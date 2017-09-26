<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates as States;

/**
 * Class ShipmentStates
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class ShipmentStates extends AbstractConstants
{
    /**
     * {@inheritdoc}
     */
    static public function getConfig()
    {
        $prefix = 'ekyna_commerce.shipment.state.';

        return [
            States::STATE_NONE        => [$prefix.States::STATE_NONE,        'default'],
            States::STATE_NEW         => [$prefix.States::STATE_NEW,         'default'],
            //States::STATE_CHECKOUT    => [$prefix.States::STATE_CHECKOUT,    'default'],
            //States::STATE_ONHOLD      => [$prefix.States::STATE_ONHOLD,      'warning'],
            States::STATE_PENDING     => [$prefix.States::STATE_PENDING,     'warning'],
            //States::STATE_BACKORDERED => [$prefix.States::STATE_BACKORDERED, 'warning'],
            States::STATE_READY       => [$prefix.States::STATE_READY,       'warning'],
            States::STATE_PARTIAL     => [$prefix.States::STATE_PARTIAL,     'warning'],
            States::STATE_SHIPPED     => [$prefix.States::STATE_SHIPPED,     'success'],
            States::STATE_COMPLETED   => [$prefix.States::STATE_COMPLETED,   'success'],
            States::STATE_RETURNED    => [$prefix.States::STATE_RETURNED,    'primary'],
            States::STATE_CANCELLED   => [$prefix.States::STATE_CANCELLED,   'danger'],
        ];
    }

    /**
     * Returns the theme for the given state.
     *
     * @param string $state
     * @return string
     */
    static public function getTheme($state)
    {
        static::isValid($state, true);

        return static::getConfig()[$state][1];
    }

    /**
     * Returns the choices for the shipment form type.
     *
     * @param array $restrict
     *
     * @return array
     */
    static function getFormChoices(array $restrict = [])
    {
        return static::getChoices(array_merge([
            States::STATE_NONE,
            States::STATE_PARTIAL
        ], $restrict));
    }
}
