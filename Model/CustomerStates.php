<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Customer\Model\CustomerStates as States;

/**
 * Class CustomerStates
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class CustomerStates extends AbstractConstants
{
    public static function getConfig(): array
    {
        $prefix = 'status.';

        return [
            States::STATE_NEW       => [$prefix . States::STATE_NEW,       'brown'],
            States::STATE_VALID     => [$prefix . States::STATE_VALID,     'light-green'],
            States::STATE_FRAUDSTER => [$prefix . States::STATE_FRAUDSTER, 'red'],
        ];
    }

    public static function getTranslationDomain(): ?string
    {
        return 'EkynaCommerce';
    }
}
