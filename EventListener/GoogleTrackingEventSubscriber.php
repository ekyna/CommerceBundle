<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Event\AddToCartEvent;
use Ekyna\Bundle\CommerceBundle\Event\CheckoutEvent;
use Ekyna\Bundle\GoogleBundle\Tracking\Commerce\Product;
use Ekyna\Bundle\GoogleBundle\Tracking\Event;
use Ekyna\Bundle\GoogleBundle\Tracking\TrackingPool;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CheckoutEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class GoogleTrackingEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var TrackingPool
     */
    private $pool;

    /**
     * @var AmountCalculatorInterface
     */
    private $calculator;


    /**
     * Constructor.
     *
     * @param TrackingPool              $pool
     * @param AmountCalculatorInterface $calculator
     */
    public function __construct(TrackingPool $pool, AmountCalculatorInterface $calculator)
    {
        $this->pool = $pool;
        $this->calculator = $calculator;
    }

    /**
     * Add to cart event handler.
     *
     * @param AddToCartEvent $event
     */
    public function onAddToCartSuccess(AddToCartEvent $event)
    {
        if (null === $saleItem = $event->getItem()) {
            return;
        }

        $trackEvent = new Event(Event::ADD_TO_CART);

        $this->buildTrackingItem($trackEvent, $saleItem);

        $this->pool->addEvent($trackEvent);
    }

    /**
     * Checkout init event handler.
     *
     * @param CheckoutEvent $event
     */
    public function onCheckoutInit(CheckoutEvent $event)
    {
        $this->buildTrackingEvent($event->getSale(), Event::BEGIN_CHECKOUT);
    }

    /**
     * Checkout step event handler.
     *
     * @param CheckoutEvent $event
     */
    public function onCheckoutStep(CheckoutEvent $event)
    {
        $this->buildTrackingEvent($event->getSale(), Event::CHECKOUT_PROGRESS);
    }

    /**
     * Checkout confirmation event handler.
     *
     * @param CheckoutEvent $event
     */
    public function onCheckoutConfirmation(CheckoutEvent $event)
    {
        // TODO Find a way to do this only once

        $sale = $event->getSale();

        $event = $this->buildTrackingEvent($sale, Event::PURCHASE);

        $result = $sale->getFinalResult();
        $shipping = $sale->getShipmentResult();
        $currency = $sale->getCurrency()->getCode();

        $event
            ->setTransactionId($sale->getNumber())
            ->setValue((string)Money::round($result->getTotal(), $currency))
            ->setTax((string)Money::round($result->getTax(), $currency))
            ->setShipping((string)Money::round($shipping->getBase(), $currency));
    }

    /**
     * Builds the tracking event.
     *
     * @param SaleInterface $sale
     * @param string        $type
     *
     * @return Event
     */
    private function buildTrackingEvent(SaleInterface $sale, string $type)
    {
        if (!$sale->getFinalResult()) {
            $this->calculator->calculateSale($sale);
        }

        $trackEvent = new Event($type);

        foreach ($sale->getItems() as $saleItem) {
            $this->buildTrackingItem($trackEvent, $saleItem);
        }

        $this->pool->addEvent($trackEvent);

        return $trackEvent;
    }

    /**
     * Builds the tracking item.
     *
     * @param Event             $trackEvent
     * @param SaleItemInterface $saleItem
     */
    private function buildTrackingItem(Event $trackEvent, SaleItemInterface $saleItem)
    {
        if (!($saleItem->isCompound() && !$saleItem->hasPrivateChildren())) {
            if (!$saleItem->getResult()) {
                $this->calculator->calculateSaleItem($saleItem);
            }

            $total = $saleItem->getResult()->getBase();
            $quantity = intval($saleItem->getTotalQuantity());

            $price = (string)Money::round($total / $quantity, $saleItem->getSale()->getCurrency()->getCode());

            $trackItem = new Product($saleItem->getReference(), $saleItem->getDesignation());
            $trackItem
                ->setPrice($price)
                ->setQuantity($quantity);

            $trackEvent->addItem($trackItem);
        }

        foreach ($saleItem->getChildren() as $child) {
            if ($child->isPrivate()) {
                continue;
            }

            $this->buildTrackingItem($trackEvent, $child);
        }
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            AddToCartEvent::SUCCESS            => ['onAddToCartSuccess', 2048],
            CheckoutEvent::EVENT_INIT          => ['onCheckoutInit', 0],
            CheckoutEvent::EVENT_SHIPMENT_STEP => ['onCheckoutStep', 0],
            CheckoutEvent::EVENT_PAYMENT_STEP  => ['onCheckoutStep', 0],
            CheckoutEvent::EVENT_CONFIRMATION  => ['onCheckoutConfirmation', 0],
        ];
    }
}
