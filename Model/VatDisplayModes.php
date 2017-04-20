<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Pricing\Model\VatDisplayModes as Modes;

/**
 * Class VatDisplayModes
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class VatDisplayModes extends AbstractConstants
{
    public static function getConfig(): array
    {
        $prefix = 'pricing.vat_display_mode.';

        return [
            Modes::MODE_NET => [$prefix.Modes::MODE_NET, 'default'],
            Modes::MODE_ATI => [$prefix.Modes::MODE_ATI, 'primary'],
        ];
    }

    public static function getTranslationDomain(): ?string
    {
        return 'EkynaCommerce';
    }
}
