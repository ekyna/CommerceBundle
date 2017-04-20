<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes as Types;
use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;

/**
 * Class AdjustmentTypes
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class AdjustmentTypes extends AbstractConstants
{
    public static function getConfig(): array
    {
        $prefix = 'adjustment.type.';

        return [
            Types::TYPE_TAXATION    => [$prefix . Types::TYPE_TAXATION],
            Types::TYPE_INCLUDED    => [$prefix . Types::TYPE_INCLUDED],
            Types::TYPE_DISCOUNT    => [$prefix . Types::TYPE_DISCOUNT],
        ];
    }

    public static function getDefaultChoice(): ?string
    {
        return Types::TYPE_DISCOUNT;
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
