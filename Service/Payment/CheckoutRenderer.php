<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Payment;

use Ekyna\Bundle\CommerceBundle\Event\CheckoutEvent;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class CheckoutRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Payment
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CheckoutRenderer
{
    private EventDispatcherInterface $dispatcher;


    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Renders the cart checkout content.
     */
    public function renderCheckoutContent(CartInterface $cart = null): string
    {
        $event = new CheckoutEvent($cart);

        $this->dispatcher->dispatch($event, CheckoutEvent::EVENT_CONTENT);

        return $event->getContent();
    }
}
