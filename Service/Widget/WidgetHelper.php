<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Widget;

use Ekyna\Bundle\CommerceBundle\Form\Type\Widget\ContextType;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\UserBundle\Model\UserInterface;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Currency\CurrencyRendererInterface;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\User\Service\UserProviderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Currencies;
use Symfony\Component\Intl\Locales;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class WidgetHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Widget
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class WidgetHelper
{
    private UserProviderInterface     $userProvider;
    private ContextProviderInterface  $contextProvider;
    private CurrencyRendererInterface $currencyRenderer;
    private UrlGeneratorInterface     $urlGenerator;
    private RequestStack              $requestStack;
    private FormFactoryInterface      $formFactory;
    private TranslatorInterface       $translator;
    private array                     $locales;
    private array                     $data;

    private ?FormInterface $contextForm = null;

    public function __construct(
        UserProviderInterface     $userProvider,
        ContextProviderInterface  $contextProvider,
        CurrencyRendererInterface $currencyRenderer,
        UrlGeneratorInterface     $urlGenerator,
        RequestStack              $requestStack,
        FormFactoryInterface      $formFactory,
        TranslatorInterface       $translator,
        array                     $locales,
        array                     $data = []
    ) {
        $this->userProvider = $userProvider;
        $this->contextProvider = $contextProvider;
        $this->currencyRenderer = $currencyRenderer;
        $this->urlGenerator = $urlGenerator;
        $this->requestStack = $requestStack;
        $this->formFactory = $formFactory;
        $this->translator = $translator;
        $this->locales = $locales;
        $this->data = $data;
    }

    public function getCustomer(): ?CustomerInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->contextProvider->getCustomerProvider()->getCustomer();
    }

    public function getUser(): ?UserInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->userProvider->getUser();
    }

    public function getCart(): ?CartInterface
    {
        return $this->contextProvider->getCartProvider()->getCart();
    }

    public function getCurrency(): ?CurrencyInterface
    {
        return $this->contextProvider->getCurrencyProvider()->getCurrency();
    }

    public function getCountry(): ?CountryInterface
    {
        return $this->contextProvider->getCountryProvider()->getCountry();
    }

    public function getLocale(): string
    {
        return $this->contextProvider->getLocalProvider()->getCurrentLocale();
    }

    /**
     * Returns the customer widget data.
     */
    public function getCustomerWidgetData(): array
    {
        $data = array_replace([
            'id'           => 'customer-widget',
            'title'        => 'widget.customer.title',
            'label'        => 'widget.customer.title',
            'trans_domain' => 'EkynaCommerce',
        ], $this->data['customer'], [
            'href' => $this->urlGenerator->generate('ekyna_user_account_index'),
            'url'  => [
                'widget'   => $this->urlGenerator->generate('ekyna_commerce_widget_customer_widget'),
                'dropdown' => $this->urlGenerator->generate('ekyna_commerce_widget_customer_dropdown'),
            ],
        ]);

        if (!empty($data['title'])) {
            $data['title'] = $this->translator->trans($data['title'], [], $data['trans_domain']);
        }

        if (!empty($data['label'])) {
            if ($customer = $this->getCustomer()) {
                $data['label'] = $customer->getFirstName() . ' ' . $customer->getLastName();
            } else {
                $data['label'] = $this->translator->trans($data['label'], [], $data['trans_domain']);
            }
        }

        return $data;
    }

    /**
     * Returns the cart widget data.
     */
    public function getCartWidgetData(): array
    {
        $data = array_replace([
            'id'           => 'cart-widget',
            'title'        => 'widget.cart.title',
            'label'        => 'widget.cart.title',
            'trans_domain' => 'EkynaCommerce',
        ], $this->data['cart'], [
            'href' => $this->urlGenerator->generate('ekyna_commerce_cart_checkout_index'),
            'url'  => [
                'widget'   => $this->urlGenerator->generate('ekyna_commerce_widget_cart_widget'),
                'dropdown' => $this->urlGenerator->generate('ekyna_commerce_widget_cart_dropdown'),
            ],
        ]);

        if (!empty($data['title'])) {
            $data['title'] = $this->translator->trans($data['title'], [], $data['trans_domain']);
        }

        if (!empty($data['label'])) {
            if (($cart = $this->getCart()) && $cart->hasItems()) {
                $count = $cart->getItems()->count();
                $count = $this
                    ->translator
                    ->trans('widget.cart.items', ['count' => $count], 'EkynaCommerce');

                $total = $this
                    ->currencyRenderer
                    ->renderQuote($cart->getGrandTotal(), $cart);

                $data['label'] = $count . ' <strong>' . $total . '</strong>';
            } else {
                $data['label'] = $this->translator->trans($data['label'], [], $data['trans_domain']);
            }
        }

        return $data;
    }

    /**
     * Returns the context widget data.
     */
    public function getContextWidgetData(Request $request = null): array
    {
        if (null === $request) {
            $request = $this->requestStack->getMainRequest();
        }

        $widgetData = [];
        if ($request) {
            $widgetData['route'] = $request->attributes->get('_route');
            $parameters = $request->attributes->get('_route_params');
            unset($parameters['_locale']);
            if (!empty($parameters)) {
                $widgetData['param'] = $parameters;
            }
        }

        $data = array_replace([
            'id'           => 'context-widget',
            'title'        => 'widget.context.title',
            'trans_domain' => 'EkynaCommerce',
            'label'        => '<span class="country-flag %1$s" title="%2$s"></span><span class="currency">%3$s</span><span class="locale">%4$s</span>',
        ], $this->data['context'], [
            'href' => 'javascript:void(0)',
            'url'  => [
                'widget'   => $this->urlGenerator->generate('ekyna_commerce_widget_context_widget'),
                'dropdown' => $this->urlGenerator->generate('ekyna_commerce_widget_context_dropdown'),
            ],
            'data' => $widgetData,
        ]);

        if (!empty($data['title'])) {
            $data['title'] = $this->translator->trans($data['title'], [], $data['trans_domain']);
        }

        if (!empty($data['label'])) {
            $currency = $this->contextProvider->getCurrencyProvider()->getCurrentCurrency();
            $country = $this->contextProvider->getCountryProvider()->getCurrentCountry();
            $locale = $this->contextProvider->getLocalProvider()->getCurrentLocale();

            $currencyLabel = Currencies::getSymbol($currency, $locale);
            $countryLabel = Countries::getName($country, $locale);
            $localeLabel = Locales::getName($locale, $locale);

            $data['label'] = sprintf(
                $data['label'],
                strtolower($country),
                $countryLabel,
                $currencyLabel,
                mb_convert_case($localeLabel, MB_CASE_TITLE)
            );
        }

        return $data;
    }

    /**
     * Handles context change.
     */
    public function handleContextChange(Request $request = null): ?Response
    {
        if (null === $request) {
            $request = $this->requestStack->getMainRequest();
        }

        $form = $this->getContextForm($request);

        $form->handleRequest($request);

        if (!($form->isSubmitted() && $form->isValid())) {
            return null;
        }

        $data = $form->getData();

        $this
            ->contextProvider
            ->changeCurrencyAndCountry($data['currency'], $data['country'], $data['locale']);

        if (empty($data['route'])) {
            return null;
        }

        $parameters = $data['param'] ?? [];
        $parameters['_locale'] = $data['locale'];

        return new RedirectResponse($this->urlGenerator->generate($data['route'], $parameters));
    }

    /**
     * Returns the context form.
     */
    public function getContextForm(Request $request = null): FormInterface
    {
        if ($this->contextForm) {
            return $this->contextForm;
        }

        return $this->contextForm = $this
            ->formFactory
            ->create(ContextType::class, $this->getContextFormData($request), [
                'method'  => 'POST',
                'action'  => $this->urlGenerator->generate('ekyna_commerce_widget_context_change'),
                'locales' => $this->locales,
            ]);
    }

    /**
     * Returns the context form data.
     */
    public function getContextFormData(Request $request = null): array
    {
        if (null === $request) {
            $request = $this->requestStack->getMainRequest();
        }

        return [
            'currency' => $this->getCurrency(),
            'country'  => $this->getCountry(),
            'locale'   => $this->getLocale(),
            'route'    => $request->query->get('route'),
            'param'    => $request->query->get('param'),
        ];
    }

    /**
     * Handles currency change.
     */
    public function handleCurrencyChange(Request $request = null): void
    {
        if (null === $request) {
            $request = $this->requestStack->getMainRequest();
        }

        // Change current currency
        if ($code = $request->request->get('currency')) {
            $this->contextProvider->changeCurrencyAndCountry($code);
        }
    }

    /**
     * Returns the currency widget data.
     */
    public function getCurrencyWidgetData(): array
    {
        $provider = $this->contextProvider->getCurrencyProvider();

        return array_replace([
            'id'         => 'currency-widget',
            'current'    => $provider->getCurrentCurrency(),
            'currencies' => $provider->getAvailableCurrencies(),
        ], $this->data['currency']);
    }
}
