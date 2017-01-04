<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Common\Transformer\SaleTransformerInterface as BaseInterface;

/**
 * Interface SaleTransformerInterface
 * @package Ekyna\Bundle\CommerceBundle\Service\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleTransformerInterface extends BaseInterface
{
    /**
     * Transforms a cart to an order.
     *
     * @param CartInterface $cart
     * @param bool          $remove Whether or not to remove the cart.
     *
     * @return OrderInterface
     */
    public function transformCartToOrder(CartInterface $cart, $remove = false);
}
