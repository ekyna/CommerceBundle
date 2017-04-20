<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Component\Commerce\Common\Model\AdjustmentModes as Modes;
use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;

/**
 * Class AdjustmentModes
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class AdjustmentModes extends AbstractConstants
{
    public static function getConfig(): array
    {
        $prefix = 'adjustment.mode.';

        return [
            Modes::MODE_FLAT    => [$prefix . Modes::MODE_FLAT],
            Modes::MODE_PERCENT => [$prefix . Modes::MODE_PERCENT],
        ];
    }

    public static function getDefaultChoice(): ?string
    {
        return Modes::MODE_PERCENT;
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
