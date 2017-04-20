<?php

declare(strict_types=1);

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
    protected CustomerProviderInterface $customerProvider;
    protected Features                  $features;

    public function __construct(CustomerProviderInterface $customerProvider, Features $features)
    {
        $this->customerProvider = $customerProvider;
        $this->features = $features;
    }

    public function onMenuConfigure(MenuEvent $event): void
    {
        $menu = $event->getMenu();

        if (!$customer = $this->customerProvider->getCustomer()) {
            return;
        }

        if ($customer->isBusiness() && $customer->getPaymentTerm()) {
            // Balance
            $menu
                ->addChild('account.balance.title', [
                    'route' => 'ekyna_commerce_account_balance_index',
                ])
                ->setExtra('translation_domain', 'EkynaCommerce');
        }

        // Quotes
        $menu
            ->addChild('account.quote.title', [
                'route' => 'ekyna_commerce_account_quote_index',
            ])
            ->setExtra('translation_domain', 'EkynaCommerce');

        // Orders
        $menu
            ->addChild('account.order.title', [
                'route' => 'ekyna_commerce_account_order_index',
            ])
            ->setExtra('translation_domain', 'EkynaCommerce');

        // Invoices
        if (!$customer->hasParent()) {
            $menu
                ->addChild('account.invoice.title', [
                    'route' => 'ekyna_commerce_account_invoice_index',
                ])
                ->setExtra('translation_domain', 'EkynaCommerce');
        }

        // Addresses
        $menu
            ->addChild('account.address.title', [
                'route' => 'ekyna_commerce_account_address_index',
            ])
            ->setExtra('translation_domain', 'EkynaCommerce');

        // Contact
        if ($this->features->getConfig(Features::CUSTOMER_CONTACT . '.account')) {
            $menu
                ->addChild('account.contact.title', [
                    'route' => 'ekyna_commerce_account_contact_index',
                ])
                ->setExtra('translation_domain', 'EkynaCommerce');
        }

        // Loyalty
        if ($this->features->isEnabled(Features::LOYALTY)) {
            $menu
                ->addChild('account.loyalty.title', [
                    'route' => 'ekyna_commerce_account_loyalty_index',
                ])
                ->setExtra('translation_domain', 'EkynaCommerce');
        }

        // Newsletter
        if ($this->features->isEnabled(Features::NEWSLETTER)) {
            $menu
                ->addChild('newsletter.title', [
                    'route' => 'ekyna_commerce_account_newsletter_index',
                ])
                ->setExtra('translation_domain', 'EkynaCommerce');
        }

        // Support
        if ($this->features->isEnabled(Features::SUPPORT)) {
            $menu
                ->addChild('account.ticket.title', [
                    'route' => 'ekyna_commerce_account_ticket_index',
                ])
                ->setExtra('translation_domain', 'EkynaCommerce');
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MenuEvent::CONFIGURE_ACCOUNT => ['onMenuConfigure', 0],
        ];
    }
}
