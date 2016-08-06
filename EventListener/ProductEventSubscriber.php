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
            ProductEvents::PRE_CREATE => ['onPreCreate', 0],
            ProductEvents::PRE_UPDATE => ['onPreUpdate', 0],
            ProductEvents::PRE_DELETE => ['onPreDelete', 0],
        ];
    }
}
