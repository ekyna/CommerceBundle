<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes as Modes;

/**
 * Class StockSubjectModes
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class StockSubjectModes extends AbstractConstants
{
    /**
     * @inheritdoc
     */
    static public function getConfig()
    {
        $prefix = 'ekyna_commerce.stock_subject.mode.';

        return [
            Modes::MODE_INHERITED    => [$prefix . Modes::MODE_INHERITED,    'default'],
            Modes::MODE_MANUAL       => [$prefix . Modes::MODE_MANUAL,       'danger'],
            Modes::MODE_AUTO         => [$prefix . Modes::MODE_AUTO,         'success'],
            Modes::MODE_JUST_IN_TIME => [$prefix . Modes::MODE_JUST_IN_TIME, 'warning'],
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
}
