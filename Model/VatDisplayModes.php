<?php

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
    /**
     * @inheritDoc
     */
    public static function getConfig()
    {
        $prefix = 'ekyna_commerce.pricing.vat_display_mode.';

        return [
            Modes::MODE_NET => [$prefix.Modes::MODE_NET, 'default'],
            Modes::MODE_ATI => [$prefix.Modes::MODE_ATI, 'primary'],
        ];
    }

    /**
     * Returns the theme for the given mode.
     *
     * @param string $mode
     *
     * @return string
     */
    static public function getTheme($mode)
    {
        static::isValid($mode, true);

        return static::getConfig()[$mode][1];
    }

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
