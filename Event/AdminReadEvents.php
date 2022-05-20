<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Event;

/**
 * Class AdminReadEvents
 * @package Ekyna\Bundle\CommerceBundle\Event
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class AdminReadEvents
{
    public const CART             = 'ekyna_commerce.cart.admin_read';
    public const CUSTOMER         = 'ekyna_commerce.customer.admin_read';
    public const ORDER            = 'ekyna_commerce.order.admin_read';
    public const QUOTE            = 'ekyna_commerce.quote.admin_read';
    public const SUPPLIER         = 'ekyna_commerce.supplier.admin_read';
    public const SUPPLIER_ORDER   = 'ekyna_commerce.supplier_order.admin_read';
    public const SUPPLIER_PRODUCT = 'ekyna_commerce.supplier_product.admin_read';
}
