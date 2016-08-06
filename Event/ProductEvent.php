<?php

namespace Ekyna\Bundle\CommerceBundle\Event;

use Ekyna\Bundle\AdminBundle\Event\ResourceEvent;
use Ekyna\Component\Commerce\Product\Model\ProductEventInterface;
use Ekyna\Component\Commerce\Product\Model\ProductInterface;

/**
 * Class ProductEvent
 * @package Ekyna\Bundle\CommerceBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductEvent extends ResourceEvent implements ProductEventInterface
{
    /**
     * Constructor.
     *
     * @param ProductInterface $product
     */
    public function __construct(ProductInterface $product)
    {
        $this->setResource($product);
    }

    /**
     * @inheritdoc
     */
    public function getProduct()
    {
        return $this->getResource();
    }
}
