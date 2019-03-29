<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\UserBundle\Event\AuthenticationEvent;
use Ekyna\Component\Commerce\Cart\Provider\CartProviderInterface;
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
     * @param UrlGeneratorInterface     $urlGenerator
     */
    public function __construct(
        CartProviderInterface $cartProvider,
        CustomerProviderInterface $customerProvider,
        CurrencyProviderInterface $currencyProvider,
        UrlGeneratorInterface     $urlGenerator
    ) {
        $this->cartProvider = $cartProvider;
        $this->customerProvider = $customerProvider;
        $this->currencyProvider = $currencyProvider;
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

        $this->configureCurrency();

        $this->checkCartOwner();
    }

    /**
     * Implicit login event handler.
     */
    public function onImplicitLogin()
    {
        // Resets the customer provider to prevent customer mismatch.
        $this->customerProvider->reset();

        $this->configureCurrency();

        $this->checkCartOwner();
    }

    /**
     * Configures the currency regarding to the customer setting.
     */
    protected function configureCurrency()
    {
        if (!$customer = $this->customerProvider->getCustomer()) {
            return;
        }

        $currency = $customer->getCurrency()->getCode();

        if ($this->currencyProvider->getCurrentCurrency() !== $currency) {
            $this->currencyProvider->setCurrentCurrency($currency);

            $this->redirect = true;
        }
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
                $cart
                    ->setCustomer($customer)
                    ->setCurrency($customer->getCurrency());

                $this->cartProvider->updateCustomerGroupAndCurrency();

                $this->cartProvider->saveCart();

                $this->redirect = true;
            }
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
