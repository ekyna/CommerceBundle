<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Component\Commerce\Cart\Provider\CartProviderInterface;
use Ekyna\Component\Commerce\Customer\Provider\CustomerProviderInterface;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * Class SecurityListener
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SecurityListener implements EventSubscriberInterface
{
    /**
     * @var CartProviderInterface
     */
    protected $cartProvider;

    /**
     * @var CustomerProviderInterface
     */
    protected $customerProvider;


    /**
     * Constructor.
     *
     * @param CartProviderInterface     $cartProvider
     * @param CustomerProviderInterface $customerProvider
     */
    public function __construct(
        CartProviderInterface $cartProvider,
        CustomerProviderInterface $customerProvider
    ) {
        $this->cartProvider = $cartProvider;
        $this->customerProvider = $customerProvider;
    }

    /**
     * Interactive login event handler.
     */
    public function onInteractiveLogin()
    {
        // Resets the customer provider to prevent customer mismatch.
        $this->customerProvider->reset();

        $this->checkCartOwner();
    }

    /**
     * Implicit login event handler.
     */
    public function onImplicitLogin()
    {
        // Resets the customer provider to prevent customer mismatch.
        $this->customerProvider->reset();

        $this->checkCartOwner();
    }

    /**
     * Checks whether or not the current (session) cart belongs to the logged user (customer).
     * If not, assigns the logged customer to the current cart.
     */
    protected function checkCartOwner()
    {
        if ($this->cartProvider->hasCart()) {
            $cart = $this->cartProvider->getCart();
            $customer = $this->customerProvider->getCustomer();

            if ($cart->getCustomer() !== $customer) {
                $cart->setCustomer($customer);

                $this->cartProvider->updateCustomerGroupAndCurrency();
                $this->cartProvider->saveCart();
            }
        }
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN      => ['onInteractiveLogin', -1024],
            FOSUserEvents::SECURITY_IMPLICIT_LOGIN => ['onImplicitLogin', -1024],
        ];
    }
}
