<?php

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
    static private $config;

    /**
     * @inheritDoc
     */
    public static function getConfig()
    {
        if (null !== static::$config) {
            return static::$config;
        }

        $label = 'ekyna_commerce.unit.%s.label';

        static::$config = [];

        foreach (Constants::getUnits() as $unit) {
            static::$config[$unit] = [
                sprintf($label, $unit),
            ];
        }

        return static::$config;
    }

    /**
     * Returns the symbol for the given unit.
     *
     * @param string $unit
     *
     * @return int
     *
     * @see Constants::getSymbol()
     */
    static function getSymbol($unit)
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
    static function getPrecision($unit)
    {
        return Constants::getPrecision($unit);
    }

    /**
     * Disabled constructor.
     *
     * @codeCoverageIgnore
     */
    final private function __construct()
    {
    }
}