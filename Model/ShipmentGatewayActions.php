<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayActions as Act;

use Symfony\Contracts\Translation\TranslatableInterface;

use function Symfony\Component\Translation\t;

/**
 * Class ShipmentGatewayActions
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentGatewayActions extends AbstractConstants
{
    public static function getConfig(): array
    {
        $prefix = 'shipment.action.';

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
     * Returns the icon for the given action.
     */
    public static function getIcon(string $action): ?string
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
     * Returns the confirmation message for the given action.
     */
    public static function getConfirm(string $action): ?TranslatableInterface
    {
        $prefix = 'shipment.confirm.';

        switch ($action) {
            case Act::CANCEL:
            case Act::COMPLETE:
                return t($prefix . $action, [], static::getTranslationDomain());
            default:
                return null;
        }
    }

    /**
     * Returns the target for the given action.
     */
    public static function getTarget(string $action): ?string
    {
        switch ($action) {
            case Act::PRINT_LABEL:
            case Act::TRACK:
            case Act::PROVE:
                return '_blank';
        }

        return null;
    }

    public static function getTranslationDomain(): ?string
    {
        return 'EkynaCommerce';
    }
}
