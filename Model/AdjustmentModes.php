<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Component\Commerce\Order\Model\AdjustmentModes as Modes;
use Ekyna\Bundle\CoreBundle\Model\AbstractConstants;

/**
 * Class AdjustmentModes
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class AdjustmentModes extends AbstractConstants
{
    /**
     * {@inheritdoc}
     */
    static public function getConfig()
    {
        $prefix = 'ekyna_commerce.adjustment.mode.';

        return [
            Modes::MODE_FLAT    => [$prefix . Modes::MODE_FLAT],
            Modes::MODE_PERCENT => [$prefix . Modes::MODE_PERCENT],
        ];
    }
}
