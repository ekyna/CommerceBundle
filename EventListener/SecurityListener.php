<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\UserBundle\Event\AuthenticationEvent;
use Ekyna\Component\Commerce\Cart\Provider\CartProviderInterface;
use Ekyna\Component\Commerce\Common\Country\CountryProviderInterface;
use Ekyna\Component\Commerce\Common\Currency\CurrencyProviderInterface;
use Ekyna\Component\Commerce\Customer\Provider\CustomerProviderInterface;
use FOS\UserBundle\FOSUserEvents;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
     * @var CurrencyProviderInterface
     */
    protected $currencyProvider;

    /**
     * @var CountryProviderInterface
     */
    protected $countryProvider;

    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * @var bool
     */
    protected $redirect = false;


    /**
     * Constructor.
     *
     * @param CartProviderInterface     $cartProvider
     * @param CustomerProviderInterface $customerProvider
     * @param CurrencyProviderInterface $currencyProvider
     * @param CountryProviderInterface  $countryProvider
     * @param UrlGeneratorInterface     $urlGenerator
     */
    public function __construct(
        CartProviderInterface $cartProvider,
        CustomerProviderInterface $customerProvider,
        CurrencyProviderInterface $currencyProvider,
        CountryProviderInterface $countryProvider,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->cartProvider = $cartProvider;
        $this->customerProvider = $customerProvider;
        $this->currencyProvider = $currencyProvider;
        $this->countryProvider = $countryProvider;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Authentication success handler.
     *
     * @param AuthenticationEvent $event
     */
    public function onAuthenticationSuccess(AuthenticationEvent $event)
    {
        $token = $event->getToken();

        $customer = $this->customerProvider->getCustomer();

        if ($token instanceof OAuthToken) {
            if ($customer) {
                $url = $this->urlGenerator->generate(
                    'ekyna_user_account_index',
                    ['_locale' => $customer->getLocale()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
            } else {
                $url = $this->urlGenerator->generate(
                    'fos_user_registration_register',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
            }

            $event->setResponse(new RedirectResponse($url));

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
        if ($targetUrl === $this->urlGenerator->generate('fos_user_security_login')) {
            $targetUrl = '/';
        }
        $event->setResponse(new RedirectResponse($targetUrl));
    }

    /**
     * Interactive login event handler.
     */
    public function onInteractiveLogin()
    {
        // Resets the customer provider to prevent customer mismatch.
        $this->customerProvider->reset();

        $this->checkCartOwner();

        $this->configureContext();
    }

    /**
     * Implicit login event handler.
     */
    public function onImplicitLogin()
    {
        // Resets the customer provider to prevent customer mismatch.
        $this->customerProvider->reset();

        $this->checkCartOwner();

        $this->configureContext();
    }

    /**
     * Checks whether or not the current (session) cart belongs to the logged user (customer).
     * If not, assigns the logged customer to the current cart.
     */
    protected function checkCartOwner()
    {
        if (!$this->cartProvider->hasCart()) {
            return;
        }

        $cart = $this->cartProvider->getCart();

        // Set cart customer
        $customer = $this->customerProvider->getCustomer();
        if ($customer !== $cart->getCustomer()) {
            $cart->setCustomer($customer);

            if ($customer) {
                $cart->setCurrency($customer->getCurrency());
            }

            $this->cartProvider->updateCustomerGroupAndCurrency();

            $this->cartProvider->saveCart();

            $this->redirect = true;
        }
    }

    /**
     * Configures the currency regarding to the customer setting.
     */
    protected function configureContext()
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
     *
     * @param string $code
     */
    protected function configureContextCurrency(string $code)
    {
        if ($code !== $this->currencyProvider->getCurrentCurrency()) {
            $this->currencyProvider->setCurrency($code);

            $this->redirect = true;
        }
    }

    /**
     * Configures the context's current country.
     *
     * @param string $code
     */
    protected function configureContextCountry(string $code)
    {
        if ($code !== $this->countryProvider->getCurrentCountry()) {
            $this->countryProvider->setCountry($code);

            $this->redirect = true;
        }
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            AuthenticationEvent::SUCCESS           => ['onAuthenticationSuccess', 0],
            SecurityEvents::INTERACTIVE_LOGIN      => ['onInteractiveLogin', -1024],
            FOSUserEvents::SECURITY_IMPLICIT_LOGIN => ['onImplicitLogin', -1024],
        ];
    }
}
