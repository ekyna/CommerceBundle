<?php

declare(strict_types=1);

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
    protected ContextProviderInterface $contextProvider;

    public function __construct(ContextProviderInterface $contextProvider)
    {
        $this->contextProvider = $contextProvider;
    }

    /**
     * Sale item initialize event handler.
     */
    public function onSaleItemInitialize(SaleItemEvent $event): void
    {
        if (null === $sale = $event->getItem()->getRootSale()) {
            return;
        }

        $this->contextProvider->getContext($sale);
    }

    /**
     * Sale item build event handler.
     */
    public function onSaleItemBuild(SaleItemEvent $event): void
    {
        $this->fixItemPrivacy($event->getItem());
    }

    /**
     * Fixes the sale item privacy.
     */
    public function fixItemPrivacy(SaleItemInterface $item): void
    {
        // Fix children first
        foreach ($item->getChildren() as $child) {
            $this->fixItemPrivacy($child);
        }

        // Skip if not private
        if (!$item->isPrivate()) {
            return;
        }

        // Parent items with public children can't be private.
        if ($item->hasPublicChildren()) {
            $item->setPrivate(false);

            return;
        }

        // Root items can't be private
        if (null === $parent = $item->getParent()) {
            $item->setPrivate(false);

            return;
        }

        // Public if different tax group than parent's one
        if ($item->getTaxGroup() !== $parent->getTaxGroup()) {
            $item->setPrivate(false);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SaleItemEvents::INITIALIZE => ['onSaleItemInitialize', 2048],
            SaleItemEvents::BUILD      => ['onSaleItemBuild', -2048],
        ];
    }
}
