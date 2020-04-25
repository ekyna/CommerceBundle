<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Event\AddToCartEvent;
use Ekyna\Bundle\CommerceBundle\Event\CheckoutEvent;
use Ekyna\Bundle\GoogleBundle\Tracking\Commerce\Product;
use Ekyna\Bundle\GoogleBundle\Tracking\Event;
use Ekyna\Bundle\GoogleBundle\Tracking\TrackingPool;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorFactory;
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
     * @var AmountCalculatorFactory
     */
    private $calculatorFactory;

    /**
     * @var string
     */
    private $defaultCurrency;

    /**
     * @var AmountCalculatorInterface
     */
    private $amountCalculator;


    /**
     * Constructor.
     *
     * @param TrackingPool            $pool
     * @param AmountCalculatorFactory $calculatorFactory
     * @param string                  $defaultCurrency
     */
    public function __construct(TrackingPool $pool, AmountCalculatorFactory $calculatorFactory, string $defaultCurrency)
    {
        $this->pool = $pool;
        $this->calculatorFactory = $calculatorFactory;
        $this->defaultCurrency = $defaultCurrency;
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

        $result = $this->getAmountCalculator()->calculateSale($sale);
        $shipping = $this->getAmountCalculator()->calculateSaleShipment($sale);

        $event
            ->setTransactionId($sale->getNumber())
            ->setValue((string)Money::round($result->getTotal(), $this->defaultCurrency))
            ->setTax((string)Money::round($result->getTax(), $this->defaultCurrency))
            ->setShipping((string)Money::round($shipping->getBase(), $this->defaultCurrency));
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
            $total = $this->getAmountCalculator()->calculateSaleItem($saleItem)->getBase();

            $quantity = intval($saleItem->getTotalQuantity());

            $price = (string)Money::round($total / $quantity, $this->defaultCurrency);

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
     * Returns the amount calculator.
     *
     * @return AmountCalculatorInterface
     */
    private function getAmountCalculator(): AmountCalculatorInterface
    {
        if ($this->amountCalculator) {
            return $this->amountCalculator;
        }

        return $this->amountCalculator = $this->calculatorFactory->create($this->defaultCurrency);
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
