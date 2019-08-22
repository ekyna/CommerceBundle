<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Event\CheckoutEvent;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class CheckoutExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CheckoutExtension extends AbstractExtension
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;


    /**
     * Constructor.
     *
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'cart_checkout_content',
                [$this, 'renderCheckoutContent'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * Renders the cart checkout content.
     *
     * @param CartInterface|null $cart
     *
     * @return string
     */
    public function renderCheckoutContent(CartInterface $cart = null)
    {
        $event = new CheckoutEvent($cart);

        $this->dispatcher->dispatch(CheckoutEvent::EVENT_CONTENT, $event);

        return $event->getContent();
    }
}
