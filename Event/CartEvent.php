<?php

namespace Ekyna\Bundle\CommerceBundle\Event;

use Ekyna\Bundle\AdminBundle\Event\ResourceEvent;
use Ekyna\Component\Commerce\Cart\Model\CartEventInterface;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;

/**
 * Class CartEvent
 * @package Ekyna\Bundle\CommerceBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartEvent extends ResourceEvent implements CartEventInterface
{
    /**
     * Constructor.
     *
     * @param CartInterface $cart
     */
    public function __construct(CartInterface $cart)
    {
        $this->setResource($cart);
    }

    /**
     * @inheritdoc
     */
    public function getCart()
    {
        return $this->getResource();
    }
}
