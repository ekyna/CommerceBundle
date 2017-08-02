<?php

namespace Acme\ProductBundle\Event;

/**
 * Class ProductEvents
 * @package Acme\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class ProductEvents
{
    const INSERT            = 'acme_product.product.insert';
    const UPDATE            = 'acme_product.product.update';
    const DELETE            = 'acme_product.product.delete';

    const STOCK_UNIT_CHANGE = 'acme_product.product.stock_unit_change';
    const STOCK_UNIT_REMOVE = 'acme_product.product.stock_unit_removal';
}
