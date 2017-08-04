<?php

namespace Acme\ProductBundle\EventListener;

use Acme\Product\EventListener\StockUnitEventSubscriber as BaseSubscriber;
use Acme\Product\Event\StockUnitEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * Class StockUnitEventSubscriber
 * @package Acme\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitEventSubscriber extends BaseSubscriber implements EventSubscriberInterface
{
    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            StockUnitEvents::INSERT => ['onInsert', 0],
            StockUnitEvents::UPDATE => ['onUpdate', 0],
            StockUnitEvents::DELETE => ['onDelete', 0],
        ];
    }
}
