<?php

namespace Acme\ProductBundle\EventListener;

use Acme\ProductBundle\Entity\Product;
use Acme\ProductBundle\Event\ProductEvents;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
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
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     */
    public function __construct(PersistenceHelperInterface $persistenceHelper)
    {
        $this->persistenceHelper = $persistenceHelper;
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    /*public function onInsert(ResourceEventInterface $event)
    {
        $product = $this->getProductFromEvent($event);

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($product);
        }
    }*/

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    /*public function onUpdate(ResourceEventInterface $event)
    {
        $product = $this->getProductFromEvent($event);

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($product);
        }
    }*/

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

        // TODO Use stock updater

        if (false) {
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

        // TODO Use stock updater

        if (false) {
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
            //ProductEvents::INSERT             => ['onInsert', 0],
            //ProductEvents::UPDATE             => ['onUpdate', 0],
            //ProductEvents::DELETE             => ['onDelete', 0],
            ProductEvents::STOCK_UNIT_CHANGE  => ['onStockUnitChange', 0],
            ProductEvents::STOCK_UNIT_REMOVAL => ['onStockUnitRemoval', 0],
            //ProductEvents::CHILD_STOCK_CHANGE => ['onChildStockChange', 0],
        ];
    }
}
