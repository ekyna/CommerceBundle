<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Widget;

use Ekyna\Bundle\UserBundle\Service\Provider\UserProviderInterface;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Util\FormatterAwareTrait;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class WidgetHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Widget
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class WidgetHelper
{
    use FormatterAwareTrait;

    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var ContextProviderInterface
     */
    private $contextProvider;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var TranslatorInterface
     */
    private $translator;


    /**
     * Constructor.
     *
     * @param UserProviderInterface    $userProvider
     * @param ContextProviderInterface $contextProvider
     * @param FormatterFactory         $formatterFactory
     * @param UrlGeneratorInterface    $urlGenerator
     * @param RequestStack             $requestStack
     * @param TranslatorInterface      $translator
     */
    public function __construct(
        UserProviderInterface $userProvider,
        ContextProviderInterface $contextProvider,
        FormatterFactory $formatterFactory,
        UrlGeneratorInterface $urlGenerator,
        RequestStack $requestStack,
        TranslatorInterface $translator
    ) {
        $this->userProvider = $userProvider;
        $this->contextProvider = $contextProvider;
        $this->formatterFactory = $formatterFactory;
        $this->urlGenerator = $urlGenerator;
        $this->requestStack = $requestStack;
        $this->translator = $translator;
    }

    /**
     * Returns the customer.
     *
     * @return \Ekyna\Component\Commerce\Customer\Model\CustomerInterface|null
     */
    public function getCustomer()
    {
        return $this->contextProvider->getCustomerProvider()->getCustomer();
    }

    /**
     * Returns the user.
     *
     * @return \Ekyna\Bundle\UserBundle\Model\UserInterface|null
     */
    public function getUser()
    {
        return $this->userProvider->getUser();
    }

    /**
     * Returns the cart.
     *
     * @return \Ekyna\Component\Commerce\Cart\Model\CartInterface|null
     */
    public function getCart()
    {
        return $this->contextProvider->getCartProvider()->getCart();
    }

    /**
     * Returns the current currency.
     *
     * @return \Ekyna\Component\Commerce\Common\Model\CurrencyInterface
     */
    public function getCurrency()
    {
        return $this->contextProvider->getCurrencyProvider()->getCurrency();
    }

    /**
     * Returns the current country.
     *
     * @return \Ekyna\Component\Commerce\Common\Model\CountryInterface
     */
    public function getCountry()
    {
        return $this->contextProvider->getCountryProvider()->getCountry();
    }

    /**
     * Returns the current locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->contextProvider->getLocalProvider()->getCurrentLocale();
    }

    /**
     * Returns the context provider.
     *
     * @return ContextProviderInterface
     */
    public function getContextProvider(): ContextProviderInterface
    {
        return $this->contextProvider;
    }

    /**
     * Returns the url generator.
     *
     * @return UrlGeneratorInterface
     */
    public function getUrlGenerator(): UrlGeneratorInterface
    {
        return $this->urlGenerator;
    }

    /**
     * Returns the customer widget data.
     *
     * @return array
     */
    public function getCustomerWidgetData()
    {
        $label = $this->translator->trans('ekyna_commerce.widget.customer.title');

        $data = [
            'id'    => 'customer-widget',
            'href'  => $this->urlGenerator->generate('ekyna_user_account_index'),
            'title' => $label,
            'label' => $label,
            'url'   => [
                'widget'   => $this->urlGenerator->generate('ekyna_commerce_widget_customer_widget'),
                'dropdown' => $this->urlGenerator->generate('ekyna_commerce_widget_customer_dropdown'),
            ],
        ];

        if (null !== $customer = $this->getCustomer()) {
            $data['label'] = $customer->getFirstName() . ' ' . $customer->getLastName();
        }

        return $data;
    }

    /**
     * Returns the cart widget data.
     *
     * @return array
     */
    public function getCartWidgetData()
    {
        $label = $this->translator->trans('ekyna_commerce.widget.cart.title');

        $data = [
            'id'    => 'cart-widget',
            'href'  => $this->urlGenerator->generate('ekyna_commerce_cart_checkout_index'),
            'title' => $label,
            'label' => $label,
            'url'   => [
                'widget'   => $this->urlGenerator->generate('ekyna_commerce_widget_cart_widget'),
                'dropdown' => $this->urlGenerator->generate('ekyna_commerce_widget_cart_dropdown'),
            ],
        ];

        $cart = $this->getCart();
        if ((null !== $cart) && $cart->hasItems()) {
            $count = $cart->getItems()->count();
            $count = $this->translator->transChoice('ekyna_commerce.widget.cart.items', $count, ['%count%' => $count]);

            $currency = $cart->getCurrency()->getCode();
            $total = $this
                ->formatterFactory
                ->create(null, $currency)
                ->currency($cart->getGrandTotal(), $currency);

            $data['label'] = $count . ' <strong>' . $total . '</strong>';
        }

        return $data;
    }

    /**
     * Returns the context widget data.
     *
     * @return array
     */
    public function getContextWidgetData()
    {
        $currency = $this->contextProvider->getCurrencyProvider()->getCurrentCurrency();
        $country = $this->contextProvider->getCountryProvider()->getCurrentCountry();
        $locale = $this->contextProvider->getLocalProvider()->getCurrentLocale();

        $currencyLabel = Intl::getCurrencyBundle()->getCurrencySymbol($currency, $locale);
        $countryLabel = Intl::getRegionBundle()->getCountryName($country, $locale);
        $localeLabel = Intl::getLocaleBundle()->getLocaleName($locale, $locale);

        $label = sprintf(
            '<span class="country-flag %s" title="%s"></span><span class="currency">%s</span><span class="locale">%s</span>',
            strtolower($country),
            $countryLabel,
            $currencyLabel,
            mb_convert_case($localeLabel, MB_CASE_TITLE)
        );

        $data = [];
        if ($request = $this->requestStack->getMasterRequest()) {
            $data['route'] = $request->attributes->get('_route');
            $parameters = $request->attributes->get('_route_params');
            unset($parameters['_locale']);
            if (!empty($parameters)) {
                $data['param'] = $parameters;
            }
        }

        return [
            'id'    => 'context-widget',
            'href'  => 'javascript:void(0)',
            'title' => $this->translator->trans('ekyna_commerce.widget.context.title'),
            'label' => $label,
            'url'   => [
                'widget'   => $this->urlGenerator->generate('ekyna_commerce_widget_context_widget'),
                'dropdown' => $this->urlGenerator->generate('ekyna_commerce_widget_context_dropdown'),
            ],
            'data'  => $data,
        ];
    }

    /**
     * Returns the currency widget data.
     *
     * @return array
     */
    public function getCurrencyWidgetData()
    {
        $provider = $this->contextProvider->getCurrencyProvider();

        return [
            'id'         => 'currency-widget',
            'current'    => $provider->getCurrentCurrency(),
            'currencies' => $provider->getAvailableCurrencies(),
        ];
    }
}
