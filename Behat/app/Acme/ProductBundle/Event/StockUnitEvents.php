<?php

namespace Acme\ProductBundle\Event;

/**
 * Class ProductStockUnitEvents
 * @package Acme\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class StockUnitEvents
{
    const INSERT = 'acme_product.stock_unit.insert';
    const UPDATE = 'acme_product.stock_unit.update';
    const DELETE = 'acme_product.stock_unit.delete';
}
