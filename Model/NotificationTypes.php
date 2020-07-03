<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Common\Model\NotificationTypes as Types;

/**
 * Class NotificationTypes
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotificationTypes extends AbstractConstants
{
    /**
     * @inheritDoc
     */
    public static function getConfig(): array
    {
        $prefix = 'ekyna_commerce.notify.type.';
        $suffix = '.label';

        return [
            Types::MANUAL             => [$prefix . Types::MANUAL . $suffix],
            Types::ORDER_ACCEPTED     => [$prefix . Types::ORDER_ACCEPTED . $suffix],
            Types::PAYMENT_AUTHORIZED => [$prefix . Types::PAYMENT_AUTHORIZED . $suffix],
            Types::PAYMENT_CAPTURED   => [$prefix . Types::PAYMENT_CAPTURED . $suffix],
            //Types::PAYMENT_EXPIRED    => [$prefix . Types::PAYMENT_EXPIRED . $suffix],
            Types::SHIPMENT_READY     => [$prefix . Types::SHIPMENT_READY . $suffix],
            Types::SHIPMENT_SHIPPED   => [$prefix . Types::SHIPMENT_SHIPPED . $suffix],
            Types::SHIPMENT_PARTIAL   => [$prefix . Types::SHIPMENT_PARTIAL . $suffix],
            Types::RETURN_PENDING     => [$prefix . Types::RETURN_PENDING . $suffix],
            Types::RETURN_RECEIVED    => [$prefix . Types::RETURN_RECEIVED . $suffix],
            //Types::QUOTE_REMIND       => [$prefix . Types::QUOTE_REMIND . $suffix],
            //Types::CART_REMIND        => [$prefix . Types::CART_REMIND . $suffix],
        ];
    }

    /**
     * @inheritDoc
     */
    public static function getTheme(string $constant): ?string
    {
        return null;
    }
}
