<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Component\Commerce\Cart\Provider\CartProviderInterface;
use Ekyna\Component\Commerce\Common\Country\CountryProviderInterface;
use Ekyna\Component\Commerce\Common\Currency\CurrencyProviderInterface;
use Ekyna\Component\Commerce\Customer\Provider\CustomerProviderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

/**
 * Class SecurityEventListener
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SecurityEventListener
{
    protected CartProviderInterface     $cartProvider;
    protected CustomerProviderInterface $customerProvider;
    protected CurrencyProviderInterface $currencyProvider;
    protected CountryProviderInterface  $countryProvider;
    protected UrlGeneratorInterface     $urlGenerator;
    protected bool                      $redirect = false;

    public function __construct(
        CartProviderInterface     $cartProvider,
        CustomerProviderInterface $customerProvider,
        CurrencyProviderInterface $currencyProvider,
        CountryProviderInterface  $countryProvider,
        UrlGeneratorInterface     $urlGenerator
    ) {
        $this->cartProvider = $cartProvider;
        $this->customerProvider = $customerProvider;
        $this->currencyProvider = $currencyProvider;
        $this->countryProvider = $countryProvider;
        $this->urlGenerator = $urlGenerator;
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        // Resets the customer provider to prevent customer mismatch.
        $this->customerProvider->reset();

        $this->checkCartOwner();

        $this->configureContext();

        if (!$this->customerProvider->getCustomer()) {
            $redirect = $this->urlGenerator->generate('ekyna_user_account_registration', [
                'target_path' => 'ekyna_user_account_index',
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            $event->setResponse(new RedirectResponse($redirect));

            return;
        }

        if (!$this->redirect) {
            return;
        }

        $request = $event->getRequest();
        if (!$request->isXmlHttpRequest()) {
            return;
        }

        $targetUrl = $request->headers->get('Referer');
        if (false !== $pos = strpos($targetUrl, '?')) {
            $targetUrl = substr($targetUrl, 0, $pos);
        }

        if (empty($targetUrl)) {
            return;
        }

        if ($targetUrl === $this->urlGenerator->generate('ekyna_user_security_login')) {
            $targetUrl = '/';
        }

        $event->setResponse(new RedirectResponse($targetUrl));
    }

    /**
     * Checks whether the current (session) cart belongs to the logged user (customer).
     * If not, assigns the logged customer to the current cart.
     */
    protected function checkCartOwner(): void
    {
        if (!$this->cartProvider->hasCart()) {
            return;
        }

        $cart = $this->cartProvider->getCart();

        // Set cart customer
        $customer = $this->customerProvider->getCustomer();
        if ($customer === $cart->getCustomer()) {
            return;
        }

        $cart->setCustomer($customer);

        if ($customer) {
            $cart->setCurrency($customer->getCurrency());
        }

        $this->cartProvider->saveCart();

        $this->redirect = true;
    }

    /**
     * Configures the currency regarding the customer setting.
     */
    private function configureContext(): void
    {
        $cart = $this->cartProvider->getCart();
        $customer = $this->customerProvider->getCustomer();

        if ($cart) {
            // Set cart's currency as current
            $this->configureContextCurrency($cart->getCurrency()->getCode());

            // Set cart's delivery country as current
            if ($country = $cart->getDeliveryCountry()) {
                $this->configureContextCountry($country->getCode());

                return;
            }
        } elseif ($customer) {
            // Set customer's currency as current
            $this->configureContextCurrency($customer->getCurrency()->getCode());
        }

        if (!$customer) {
            return;
        }

        // Set customer's delivery country as current
        if (null !== $address = $customer->getDefaultDeliveryAddress(true)) {
            $this->configureContextCountry($address->getCountry()->getCode());
        }
    }

    /**
     * Configures the context's current currency.
     */
    private function configureContextCurrency(string $code): void
    {
        if ($code === $this->currencyProvider->getCurrentCurrency()) {
            return;
        }

        $this->currencyProvider->setCurrency($code);

        $this->redirect = true;
    }

    /**
     * Configures the context's current country.
     */
    private function configureContextCountry(string $code): void
    {
        if ($code === $this->countryProvider->getCurrentCountry()) {
            return;
        }

        $this->countryProvider->setCountry($code);

        $this->redirect = true;
    }
}
