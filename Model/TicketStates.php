<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Support\Model\TicketStates as States;

/**
 * Class TicketStates
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class TicketStates extends AbstractConstants
{
    public static function getConfig(): array
    {
        $prefix = 'status.';

        return [                                                        // User       Admin
            States::STATE_NEW      => [$prefix . States::STATE_NEW,      'brown',   'brown'],
            States::STATE_OPENED   => [$prefix . States::STATE_OPENED,   'success', 'warning'], // Waiting for admin reply
            States::STATE_PENDING  => [$prefix . States::STATE_PENDING,  'warning', 'success'], // Waiting for customer reply
            States::STATE_INTERNAL => [$prefix . States::STATE_INTERNAL, 'purple',  'purple'],
            States::STATE_CLOSED   => [$prefix . States::STATE_CLOSED,   'default', 'default'],
        ];
    }

    public static function getTheme(string $constant, bool $admin = false): ?string
    {
        TicketStates::isValid($constant, true);

        return TicketStates::getConfig()[$constant][$admin ? 2 : 1];
    }

    public static function getTranslationDomain(): ?string
    {
        return 'EkynaCommerce';
    }
}
