<?php

declare(strict_types=1);

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
    public static function getConfig(): array
    {
        $prefix = 'stock_subject.mode.';

        return [
            Modes::MODE_DISABLED     => [$prefix . Modes::MODE_DISABLED,     'red'],
            Modes::MODE_MANUAL       => [$prefix . Modes::MODE_MANUAL,       'orange'],
            Modes::MODE_AUTO         => [$prefix . Modes::MODE_AUTO,         'teal'],
            Modes::MODE_JUST_IN_TIME => [$prefix . Modes::MODE_JUST_IN_TIME, 'purple'],
        ];
    }

    public static function getTranslationDomain(): ?string
    {
        return 'EkynaCommerce';
    }
}
