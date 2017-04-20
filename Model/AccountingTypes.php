<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Accounting\Model\AccountingTypes as Types;

/**
 * Class AccountingTypes
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class AccountingTypes extends AbstractConstants
{
    public static function getConfig(): array
    {
        $prefix = 'accounting.type.';

        return [
            Types::TYPE_GOOD       => [$prefix . Types::TYPE_GOOD],
            //Types::TYPE_SERVICE  => [$prefix . Types::TYPE_SERVICE],
            Types::TYPE_SHIPPING   => [$prefix . Types::TYPE_SHIPPING],
            Types::TYPE_TAX        => [$prefix . Types::TYPE_TAX],
            Types::TYPE_PAYMENT    => [$prefix . Types::TYPE_PAYMENT],
            Types::TYPE_UNPAID     => [$prefix . Types::TYPE_UNPAID],
            Types::TYPE_EX_GAIN    => [$prefix . Types::TYPE_EX_GAIN],
            Types::TYPE_EX_LOSS    => [$prefix . Types::TYPE_EX_LOSS],
            Types::TYPE_ADJUSTMENT => [$prefix . Types::TYPE_ADJUSTMENT],
        ];
    }

    public static function getTheme(string $constant): ?string
    {
        return null;
    }

    public static function getTranslationDomain(): ?string
    {
        return 'EkynaCommerce';
    }
}
