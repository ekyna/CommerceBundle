<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes as Types;
use Ekyna\Bundle\CoreBundle\Model\AbstractConstants;

/**
 * Class AdjustmentTypes
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class AdjustmentTypes extends AbstractConstants
{
    /**
     * {@inheritdoc}
     */
    static public function getConfig()
    {
        $prefix = 'ekyna_commerce.adjustment.type.';

        return [
            Types::TYPE_TAXATION    => [$prefix . Types::TYPE_TAXATION],
            Types::TYPE_INCLUDED    => [$prefix . Types::TYPE_INCLUDED],
            Types::TYPE_DISCOUNT    => [$prefix . Types::TYPE_DISCOUNT],
        ];
    }

    /**
     * @return string
     */
    static public function getDefaultChoice()
    {
        return Types::TYPE_DISCOUNT;
    }
}
