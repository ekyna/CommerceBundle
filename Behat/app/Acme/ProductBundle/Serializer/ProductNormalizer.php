<?php

namespace Acme\ProductBundle\Serializer;

use Acme\ProductBundle\Entity\Product;
use Ekyna\Component\Resource\Serializer\AbstractResourceNormalizer;

/**
 * Class ProductNormalizer
 * @package Acme\ProductBundle\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductNormalizer extends AbstractResourceNormalizer
{
    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Product;
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && $type === Product::class;
    }
}
