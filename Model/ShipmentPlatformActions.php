<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Shipment\Gateway\PlatformActions;

/**
 * Class ShipmentPlatformActions
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentPlatformActions extends AbstractConstants
{
    /**
     * @inheritDoc
     */
    public static function getConfig()
    {
        $labelPrefix = 'ekyna_commerce.shipment.action.';

        return [
            PlatformActions::EXPORT       => [$labelPrefix . PlatformActions::EXPORT,       'default', null],
            PlatformActions::IMPORT       => [$labelPrefix . PlatformActions::IMPORT,       'default', null],
            PlatformActions::SHIP         => [$labelPrefix . PlatformActions::SHIP,         'default', null],
            PlatformActions::CANCEL       => [$labelPrefix . PlatformActions::CANCEL,       'default', null],
            PlatformActions::PRINT_LABELS => [$labelPrefix . PlatformActions::PRINT_LABELS, 'default', null],
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

        return 'default';
    }

    /**
     * Returns the confirmation message for the given action.
     *
     * @param $action
     *
     * @return string|null
     */
    public static function getConfirm($action)
    {
        if (static::isValid($action)) {
            return static::getConfig()[$action][2];
        }

        return null;
    }
}
