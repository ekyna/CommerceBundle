<?php

namespace Acme\ProductBundle\EventListener;

use Acme\ProductBundle\Entity\StockUnit;
use Acme\ProductBundle\Event\ProductEvents;
use Acme\ProductBundle\Event\StockUnitEvents;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stock\EventListener\AbstractStockUnitListener;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class StockUnitEventSubscriber
 * @package Acme\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitEventSubscriber extends AbstractStockUnitListener implements EventSubscriberInterface
{
    /**
     * @inheritdoc
     */
    protected function getStockUnitFromEvent(ResourceEventInterface $event)
    {
        $stockUnit = $event->getResource();

        if (!$stockUnit instanceof StockUnit) {
            throw new InvalidArgumentException("Expected instance of StockUnit.");
        }

        return $stockUnit;
    }

    /**
     * @inheritdoc
     */
    protected function getSubjectStockUnitChangeEventName()
    {
        return ProductEvents::STOCK_UNIT_CHANGE;
    }

    /**
     * @inheritdoc
     */
    protected function getSubjectStockUnitRemovalEventName()
    {
        return ProductEvents::STOCK_UNIT_REMOVAL;
    }

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
