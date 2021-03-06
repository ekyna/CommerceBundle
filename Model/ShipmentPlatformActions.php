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
    public static function getConfig(): array
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
     * Returns the confirmation message for the given action.
     *
     * @param $action
     *
     * @return string|null
     */
    public static function getConfirm(string $action): ?string
    {
        if (static::isValid($action)) {
            return static::getConfig()[$action][2];
        }

        return null;
    }
}
