<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
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
     * @var ContextProviderInterface
     */
    protected $contextProvider;


    /**
     * Constructor.
     *
     * @param ContextProviderInterface $contextProvider
     */
    public function __construct(ContextProviderInterface $contextProvider)
    {
        $this->contextProvider = $contextProvider;
    }

    /**
     * Sale item initialize event handler.
     *
     * @param SaleItemEvent $event
     */
    public function onSaleItemInitialize(SaleItemEvent $event)
    {
        if (null === $sale = $event->getItem()->getSale()) {
            return;
        }

        $this->contextProvider->getContext($sale); // TODO Admin / fallback
    }

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
        // Fix children first
        foreach ($item->getChildren() as $child) {
            $this->fixItemPrivacy($child);
        }

        // Parent items with public children can't be private.
        if ($item->isPrivate() && $item->hasPublicChildren()) {
            $item->setPrivate(false);
        }
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            SaleItemEvents::INITIALIZE => ['onSaleItemInitialize', 2048],
            SaleItemEvents::BUILD      => ['onSaleItemBuild', -2048],
        ];
    }
}
