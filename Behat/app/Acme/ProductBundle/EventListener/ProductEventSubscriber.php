<?php

namespace Acme\ProductBundle\EventListener;

use Acme\Product\Event\ProductEvents;
use Acme\Product\EventListener\ProductEventSubscriber as BaseSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductEventSubscriber
 * @package Acme\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductEventSubscriber extends BaseSubscriber implements EventSubscriberInterface
{
    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            ProductEvents::INSERT            => ['onInsert', 0],
            ProductEvents::UPDATE            => ['onUpdate', 0],
            ProductEvents::STOCK_UNIT_CHANGE => ['onStockUnitChange', 0],
        ];
    }
}
