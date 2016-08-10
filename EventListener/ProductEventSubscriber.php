<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Component\Commerce\Product\Event\ProductEvents;
use Ekyna\Component\Commerce\Product\EventListener\ProductListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductEventSubscriber extends ProductListener implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ProductEvents::INSERT => ['onInsert', 0],
            ProductEvents::UPDATE => ['onUpdate', 0],
            ProductEvents::DELETE => ['onDelete', 0],
        ];
    }
}
