<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Common\Model\Units as Constants;

/**
 * Class Units
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class Units extends AbstractConstants
{
    private static ?array $config = null;


    public static function getConfig(): array
    {
        if (null !== Units::$config) {
            return Units::$config;
        }

        $label = 'unit.%s.plural';

        Units::$config = [];

        foreach (Constants::getUnits() as $unit) {
            Units::$config[$unit] = [
                sprintf($label, $unit),
            ];
        }

        return Units::$config;
    }

    public static function getTheme(string $constant): ?string
    {
        return null;
    }

    /**
     * Returns the symbol for the given unit.
     *
     * @param string $unit
     *
     * @return string
     *
     * @see Constants::getSymbol()
     */
    public static function getSymbol(string $unit): string
    {
        return Constants::getSymbol($unit);
    }

    /**
     * Returns the rounding precision for the given unit.
     *
     * @param string $unit
     *
     * @return int
     *
     * @see Constants::getPrecision()
     */
    public static function getPrecision(string $unit): int
    {
        return Constants::getPrecision($unit);
    }

    /**
     * Returns the (translation) format for the given unit.
     *
     * @param string $unit
     *
     * @return string
     *
     * @see Constants::getSymbol()
     */
    public static function getFormat(string $unit): string
    {
        Constants::isValid($unit, true);

        if (Units::hasTranslatableFormat($unit)) {
            return sprintf('unit.%s.format', $unit);
        }

        return '%s ' . Constants::getSymbol($unit);
    }

    /**
     * Returns the units with a translatable display format.
     *
     * @param string $unit
     *
     * @return bool
     */
    public static function hasTranslatableFormat(string $unit): bool
    {
        Constants::isValid($unit, true);

        return in_array($unit, [
            Constants::INCH,
            Constants::FOOT,
            Constants::DAY,
            Constants::HOUR,
            Constants::MINUTE
        ], true);
    }

    public static function getTranslationDomain(): ?string
    {
        return 'EkynaCommerce';
    }
}
