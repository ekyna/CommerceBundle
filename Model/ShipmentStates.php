<?php

declare(strict_types=1);

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
    public static function getConfig(): array
    {
        $prefix = 'status.';

        return [
            // Common
            States::STATE_NEW         => [$prefix . States::STATE_NEW, 'brown'],
            States::STATE_CANCELED    => [$prefix . States::STATE_CANCELED, 'default'],
            // Shipment
            States::STATE_PREPARATION => [$prefix . States::STATE_PREPARATION, 'success'],
            States::STATE_READY       => [$prefix . States::STATE_READY, 'light-blue'],
            States::STATE_SHIPPED     => [$prefix . States::STATE_SHIPPED, 'teal'],
            // Return
            States::STATE_PENDING     => [$prefix . States::STATE_PENDING, 'orange'],
            States::STATE_RETURNED    => [$prefix . States::STATE_RETURNED, 'indigo'],
            // For Sale
            States::STATE_NONE        => [$prefix . States::STATE_NONE, 'default'],
            States::STATE_PARTIAL     => [$prefix . States::STATE_PARTIAL, 'purple'],
            States::STATE_COMPLETED   => [$prefix . States::STATE_COMPLETED, 'teal'],
        ];
    }

    /**
     * Returns the available state choices for the shipment form type.
     *
     * @param bool $return
     * @param bool $restrict
     *
     * @return array
     */
    public static function getFormChoices(bool $return = false, bool $restrict = false): array
    {
        $states = [States::STATE_NEW, States::STATE_CANCELED];

        if (!$return) {
            $states[] = States::STATE_PREPARATION;
        }

        if (!$restrict) {
            if ($return) {
                $states[] = States::STATE_PENDING;
                $states[] = States::STATE_RETURNED;
            } else {
                $states[] = States::STATE_READY; // TODO If method is pickup at warehouse
                $states[] = States::STATE_SHIPPED;
            }
        }

        return ShipmentStates::getChoices($states, 1);
    }

    public static function getTranslationDomain(): ?string
    {
        return 'EkynaCommerce';
    }
}
