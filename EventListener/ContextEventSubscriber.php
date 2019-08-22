<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Component\Commerce\Cart\Provider\CartProviderInterface;
use Ekyna\Component\Commerce\Common\Event\ContextChangeEvent;
use Ekyna\Component\Commerce\Common\Event\ContextEvent;
use Ekyna\Component\Commerce\Common\Event\ContextEvents;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Updater\SaleUpdaterInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class ContextEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ContextEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var CartProviderInterface
     */
    protected $cartProvider;

    /**
     * @var SaleUpdaterInterface
     */
    protected $saleUpdater;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorisationChecker;


    /**
     * Constructor.
     *
     * @param CartProviderInterface         $cartProvider
     * @param SaleUpdaterInterface          $saleUpdater
     * @param TokenStorageInterface         $tokenStorage
     * @param AuthorizationCheckerInterface $authorisationChecker
     */
    public function __construct(
        CartProviderInterface $cartProvider,
        SaleUpdaterInterface $saleUpdater,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorisationChecker
    ) {
        $this->cartProvider = $cartProvider;
        $this->saleUpdater = $saleUpdater;
        $this->tokenStorage = $tokenStorage;
        $this->authorisationChecker = $authorisationChecker;
    }

    /**
     * Context change event handler.
     *
     * @param ContextChangeEvent $event
     */
    public function onContextChange(ContextChangeEvent $event): void
    {
        if (!$this->cartProvider->hasCart()) {
            return;
        }

        $cart = $this->cartProvider->getCart();

        if ($cart->isLocked()) {
            return;
        }

        $changed = false;

        if ($currency = $event->getCurrency()) {
            $changed |= $this->onCurrencyChange($currency);
        }

        if ($country = $event->getCountry()) {
            $changed |= $this->onCountryChange($country);
        }

        if ($locale = $event->getLocale()) {
            $changed |= $this->onLocaleChange($locale);
        }

        if ($changed) {
            $this->cartProvider->saveCart();
        }
    }

    /**
     * On context currency change.
     *
     * @param CurrencyInterface $currency
     *
     * @return bool
     */
    private function onCurrencyChange(CurrencyInterface $currency): bool
    {
        $cart = $this->cartProvider->getCart();

        if ($cart->getCurrency() === $currency) {
            return false;
        }

        $cart->setCurrency($currency);

        return true;
    }

    /**
     * On context country change.
     *
     * @param CountryInterface $country
     *
     * @return bool
     */
    private function onCountryChange(CountryInterface $country): bool
    {
        $cart = $this->cartProvider->getCart();

        // If cart does not have a delivery address (country), shipping cost
        // and taxation are calculated based on the context country.

        $address = $cart->isSameAddress() ? $cart->getInvoiceAddress() : $cart->getDeliveryAddress();
        if ($address) {
            return false;
        }

        $this->saleUpdater->recalculate($cart);

        return true;
    }

    /**
     * On context locale change.
     *
     * @param string $locale
     *
     * @return bool
     */
    private function onLocaleChange(string $locale): bool
    {
        $cart = $this->cartProvider->getCart();

        if ($cart->getLocale() === $locale) {
            return false;
        }

        $cart->setLocale($locale);

        return true;
    }

    /**
     * Context build event handler.
     *
     * @param ContextEvent $event
     */
    public function onContextBuild(ContextEvent $event): void
    {
        $context = $event->getContext();

        if (null === $this->tokenStorage->getToken()) {
            return;
        }

        if (!$this->authorisationChecker->isGranted('ROLE_ADMIN')) {
            return;
        }

        $context->setAdmin(true);
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            ContextEvents::CHANGE => ['onContextChange'],
            ContextEvents::BUILD  => ['onContextBuild'],
        ];
    }
}
