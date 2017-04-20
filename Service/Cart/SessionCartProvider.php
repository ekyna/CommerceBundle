<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Cart;

use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Provider\AbstractCartProvider;
use Ekyna\Component\Commerce\Cart\Provider\CartProviderInterface;
use Ekyna\Component\Commerce\Cart\Repository\CartRepositoryInterface;
use Ekyna\Component\Commerce\Common\Currency\CurrencyProviderInterface;
use Ekyna\Component\Commerce\Customer\Provider\CustomerProviderInterface;
use Ekyna\Component\Resource\Factory\ResourceFactoryInterface;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;

use function intval;
use function is_null;

/**
 * Class SessionCartProvider
 * @package Ekyna\Bundle\CommerceBundle\Service\Cart
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SessionCartProvider extends AbstractCartProvider implements CartProviderInterface
{
    public const KEY = 'cart_id';

    protected RequestStack $requestStack;
    protected string       $key;
    protected bool         $initialized = false;


    public function __construct(
        ResourceFactoryInterface $cartFactory,
        CartRepositoryInterface $cartRepository,
        ResourceManagerInterface $cartManager,
        CustomerProviderInterface $customerProvider,
        CurrencyProviderInterface $currencyProvider,
        LocaleProviderInterface $localeProvider,
        RequestStack $requestStack,
        string $key = self::KEY
    ) {
        parent::__construct(
            $cartFactory,
            $cartRepository,
            $cartManager,
            $customerProvider,
            $currencyProvider,
            $localeProvider
        );

        $this->requestStack = $requestStack;
        $this->key = $key;
    }

    public function hasCart(): bool
    {
        $this->initialize();

        return parent::hasCart();
    }

    public function getCart(bool $create = false): ?CartInterface
    {
        $this->initialize();

        return parent::getCart($create);
    }

    public function clearCart(): CartProviderInterface
    {
        parent::clearCart();

        if (is_null($this->cart)) {
            try {
                $this->requestStack->getSession()->set($this->key, null);
            } catch (SessionNotFoundException $exception) {
            }
        }

        return $this;
    }

    public function saveCart(): CartProviderInterface
    {
        parent::saveCart();

        try {
            $this->requestStack->getSession()->set($this->key, $this->cart->getId());
        } catch (SessionNotFoundException $exception) {
        }

        return $this;
    }

    /**
     * Initializes the cart regarding to the session data or the current customer.
     */
    protected function initialize(): void
    {
        if (!$this->initialized) {
            $this->initialized = true;

            try {
                $session = $this->requestStack->getSession();
            } catch (SessionNotFoundException $exception) {
                $this->clearCart();

                return;
            }

            // By session id
            $id = intval($session->get($this->key, 0));
            if (0 < $id) {
                if (null !== $cart = $this->cartRepository->findOneById($id)) {
                    $this->setCart($cart);

                    return;
                }
            }

            // By customer
            if ($this->customerProvider->hasCustomer()) {
                /** @var CartInterface $cart */
                $cart = $this->cartRepository->findLatestByCustomer(
                    $this->customerProvider->getCustomer()
                );
                if (null !== $cart) {
                    $this->setCart($cart);

                    return;
                }
            }

            $this->clearCart();
        }
    }
}
