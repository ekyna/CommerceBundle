<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\CoreBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Product\Model\ProductInterface;
use Ekyna\Component\Commerce\Product\Model\ProductTypes as Types;

/**
 * Class ProductTypes
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class ProductTypes extends AbstractConstants
{
    /**
     * {@inheritdoc}
     */
    static public function getConfig()
    {
        $prefix = 'ekyna_commerce.product.type.';

        return [
            Types::TYPE_SIMPLE       => [$prefix . Types::TYPE_SIMPLE],
            Types::TYPE_VARIABLE     => [$prefix . Types::TYPE_VARIABLE],
            Types::TYPE_VARIANT      => [$prefix . Types::TYPE_VARIANT],
            Types::TYPE_BUNDLE       => [$prefix . Types::TYPE_BUNDLE],
            Types::TYPE_CONFIGURABLE => [$prefix . Types::TYPE_CONFIGURABLE],
        ];
    }
}
