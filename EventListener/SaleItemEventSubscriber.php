<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Component\Commerce\Common\Event\SaleItemEvent;
use Ekyna\Component\Commerce\Common\Event\SaleItemEvents;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class SaleItemEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemEventSubscriber implements EventSubscriberInterface
{
    /**
     * Sale item build event handler.
     *
     * @param SaleItemEvent $event
     */
    public function onSaleItemBuild(SaleItemEvent $event)
    {
        $this->fixItemPrivacy($event->getItem());
    }

    /**
     * Fixes the sale item privacy.
     *
     * @param SaleItemInterface $item
     */
    public function fixItemPrivacy(SaleItemInterface $item)
    {
        // Parent items can't be private.
        if ($item->isPrivate() && $item->hasChildren()) {
            $item->setPrivate(false);
        }

        foreach ($item->getChildren() as $child) {
            $this->fixItemPrivacy($child);
        }
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            SaleItemEvents::BUILD => ['onSaleItemBuild', -2048],
        ];
    }
}
