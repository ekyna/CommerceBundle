<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Component\Commerce\Cart\Provider\CartProviderInterface;
use Ekyna\Component\Commerce\Customer\Provider\CustomerProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

/**
 * Class LogoutEventListener
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class LogoutEventSubscriber implements EventSubscriberInterface
{
    private CartProviderInterface $cartProvider;
    private CustomerProviderInterface $customerProvider;


    public function __construct(CartProviderInterface $cartProvider, CustomerProviderInterface $customerProvider)
    {
        $this->cartProvider = $cartProvider;
        $this->customerProvider = $customerProvider;
    }

    public function onLogout(): void
    {
        $this->customerProvider->clear();

        if (!$this->cartProvider->hasCart()) {
            return;
        }

        $this
            ->cartProvider
            ->clearInformation()
            ->saveCart();
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => 'onLogout',
        ];
    }
}
