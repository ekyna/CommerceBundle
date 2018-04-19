<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayActions as Act;

/**
 * Class ShipmentGatewayActions
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentGatewayActions extends AbstractConstants
{
    /**
     * @inheritDoc
     */
    public static function getConfig()
    {
        $prefix = 'ekyna_commerce.shipment.action.';

        return [
            Act::SHIP              => [$prefix . Act::SHIP, 'primary'],
            Act::CANCEL            => [$prefix . Act::CANCEL, 'danger'],
            Act::COMPLETE          => [$prefix . Act::COMPLETE, 'success'],
            //Act::TRACK             => [$prefix . Act::TRACK, 'primary'],
            //Act::PROVE             => [$prefix . Act::PROVE, 'primary'],
            Act::PRINT_LABEL       => [$prefix . Act::PRINT_LABEL, 'primary'],
            Act::LIST_RELAY_POINTS => [$prefix . Act::LIST_RELAY_POINTS, 'primary'],
            Act::GET_RELAY_POINT   => [$prefix . Act::GET_RELAY_POINT, 'primary'],
        ];
    }

    /**
     * Returns the theme for the given action.
     *
     * @param string $action
     *
     * @return string
     */
    public static function getTheme($action)
    {
        if (static::isValid($action)) {
            return static::getConfig()[$action][1];
        }

        return 'primary';
    }

    /**
     * Returns the icon for the given action.
     *
     * @param string $action
     *
     * @return string
     */
    public static function getIcon($action)
    {
        switch ($action) {
            case Act::PRINT_LABEL:
                return 'barcode';
            case Act::CANCEL:
                return 'remove';
            case Act::SHIP:
                return 'road';
            case Act::COMPLETE:
                return 'ok';
        }

        return null;
    }

    /**
     * Returns the confirm for the given action.
     *
     * @param string $action
     *
     * @return string
     */
    public static function getConfirm($action)
    {
        $prefix = 'ekyna_commerce.shipment.confirm.';

        switch ($action) {
            case Act::CANCEL:
                return $prefix . $action;
            case Act::COMPLETE:
                return $prefix . $action;
        }

        return null;
    }

    /**
     * Returns the target for the given action.
     *
     * @param string $action
     *
     * @return string
     */
    public static function getTarget($action)
    {
        switch ($action) {
            case Act::PRINT_LABEL:
            case Act::TRACK:
            case Act::PROVE:
                return '_blank';
        }

        return null;
    }
}
