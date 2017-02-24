<?php

namespace Acme\ProductBundle\EventListener;

use Acme\ProductBundle\Entity\Product;
use Acme\ProductBundle\Event\ProductEvents;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stock\Updater\StockSubjectUpdaterInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductEventSubscriber
 * @package Acme\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var StockSubjectUpdaterInterface
     */
    private $stockUpdater;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface   $persistenceHelper
     * @param StockSubjectUpdaterInterface $stockUpdater
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        StockSubjectUpdaterInterface $stockUpdater
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->stockUpdater = $stockUpdater;
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $product = $this->getProductFromEvent($event);

        $changed = $this->stockUpdater->update($product);

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($product);
        }
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $product = $this->getProductFromEvent($event);

        $changed = false;

        $properties = ['inStock', 'orderedStock', 'shippedStock', 'estimatedDateOfArrival'];
        if ($this->persistenceHelper->isChanged($product, $properties)) {
            $changed = $this->stockUpdater->updateStockState($product);
        }

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($product);
        }
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    /*public function onDelete(ResourceEventInterface $event)
    {

    }*/

    /**
     * Stock unit change event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onStockUnitChange(ResourceEventInterface $event)
    {
        $product = $this->getProductFromEvent($event);

        if (null !== $stockUnit = $event->getData('stock_unit')) {
            $changed = $this->stockUpdater->updateFromStockUnitChange($product, $stockUnit);
        } else {
            $changed = $this->stockUpdater->update($product);
        }

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($product);
        }
    }

    /**
     * Stock unit delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onStockUnitRemoval(ResourceEventInterface $event)
    {
        $product = $this->getProductFromEvent($event);

        if (null !== $stockUnit = $event->getData('stock_unit')) {
            $changed = $this->stockUpdater->updateFromStockUnitRemoval($product, $stockUnit);
        } else {
            $changed = $this->stockUpdater->update($product);
        }

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($product);
        }
    }

    /**
     * Returns the product from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return Product
     */
    private function getProductFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof Product) {
            throw new InvalidArgumentException('Expected Product');
        }

        return $resource;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            ProductEvents::INSERT             => ['onInsert', 0],
            ProductEvents::UPDATE             => ['onUpdate', 0],
            //ProductEvents::DELETE             => ['onDelete', 0],
            ProductEvents::STOCK_UNIT_CHANGE  => ['onStockUnitChange', 0],
            ProductEvents::STOCK_UNIT_REMOVAL => ['onStockUnitRemoval', 0],
        ];
    }
}
