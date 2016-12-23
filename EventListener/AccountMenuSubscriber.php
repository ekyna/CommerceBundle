<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\UserBundle\Event\MenuEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AccountMenuSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AccountMenuSubscriber implements EventSubscriberInterface
{
    /**
     * Menu configure event handler.
     *
     * @param MenuEvent $event
     */
    public function onMenuConfigure(MenuEvent $event)
    {
        $menu = $event->getMenu();

        // Information
        $menu->addChild('ekyna_commerce.account.information.title', [
            'route' => 'ekyna_commerce_account_information_index',
        ]);

        // Addresses
        $menu->addChild('ekyna_commerce.account.address.title', [
            'route' => 'ekyna_commerce_account_address_index',
        ]);

        // Orders
        $menu->addChild('ekyna_commerce.account.order.title', [
            'route' => 'ekyna_commerce_account_order_index',
        ]);

        // Quotes
        $menu->addChild('ekyna_commerce.account.quote.title', [
            'route' => 'ekyna_commerce_account_quote_index',
        ]);
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            MenuEvent::CONFIGURE => ['onMenuConfigure', 0],
        ];
    }
}
