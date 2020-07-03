<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\UserBundle\Event\MenuEvent;
use Ekyna\Component\Commerce\Customer\Provider\CustomerProviderInterface;
use Ekyna\Component\Commerce\Features;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AccountMenuSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AccountMenuSubscriber implements EventSubscriberInterface
{
    /**
     * @var CustomerProviderInterface
     */
    protected $customerProvider;

    /**
     * @var Features
     */
    protected $features;


    /**
     * Constructor.
     *
     * @param CustomerProviderInterface $customerProvider
     * @param Features                  $features
     */
    public function __construct(CustomerProviderInterface $customerProvider, Features $features)
    {
        $this->customerProvider = $customerProvider;
        $this->features = $features;
    }

    /**
     * Menu configure event handler.
     *
     * @param MenuEvent $event
     */
    public function onMenuConfigure(MenuEvent $event)
    {
        $menu = $event->getMenu();

        if (!$customer = $this->customerProvider->getCustomer()) {
            return;
        }

        // Information
        $menu->addChild('ekyna_commerce.account.information.title', [
            'route' => 'ekyna_commerce_account_information_index',
        ]);

        if ($customer->isBusiness() && $customer->getPaymentTerm()) {
            // Balance
            $menu->addChild('ekyna_commerce.account.balance.title', [
                'route' => 'ekyna_commerce_account_balance_index',
            ]);
        }

        // Quotes
        $menu->addChild('ekyna_commerce.account.quote.title', [
            'route' => 'ekyna_commerce_account_quote_index',
        ]);

        // Orders
        $menu->addChild('ekyna_commerce.account.order.title', [
            'route' => 'ekyna_commerce_account_order_index',
        ]);

        // Invoices
        if (!$customer->hasParent()) {
            $menu->addChild('ekyna_commerce.account.invoice.title', [
                'route' => 'ekyna_commerce_account_invoice_index',
            ]);
        }

        // Addresses
        $menu->addChild('ekyna_commerce.account.address.title', [
            'route' => 'ekyna_commerce_account_address_index',
        ]);

        // Contact
        if ($this->features->getConfig(Features::CUSTOMER_CONTACT . '.account')) {
            $menu->addChild('ekyna_commerce.account.contact.title', [
                'route' => 'ekyna_commerce_account_contact_index',
            ]);
        }

        // Loyalty
        if ($this->features->isEnabled(Features::LOYALTY)) {
            $menu->addChild('ekyna_commerce.account.loyalty.title', [
                'route' => 'ekyna_commerce_account_loyalty_index',
            ]);
        }

        // Newsletter
        if ($this->features->isEnabled(Features::NEWSLETTER)) {
            $menu->addChild('ekyna_commerce.newsletter.title', [
                'route' => 'ekyna_commerce_account_newsletter_index',
            ]);
        }

        // Support
        if ($this->features->isEnabled(Features::SUPPORT)) {
            $menu->addChild('ekyna_commerce.account.ticket.title', [
                'route' => 'ekyna_commerce_account_ticket_index',
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            MenuEvent::CONFIGURE_ACCOUNT => ['onMenuConfigure', 0],
        ];
    }
}
