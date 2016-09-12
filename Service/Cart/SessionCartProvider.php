<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Cart;

use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Provider\CartProviderInterface;
use Ekyna\Component\Commerce\Cart\Repository\CartRepositoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class SessionCartProvider
 * @package Ekyna\Bundle\CommerceBundle\Service\Cart
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SessionCartProvider implements CartProviderInterface
{
    const KEY = 'cart_id';

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var CartRepositoryInterface
     */
    protected $repository;

    /**
     * @var CartInterface
     */
    protected $cart;

    /**
     * @var string
     */
    protected $key;


    /**
     * Constructor.
     *
     * @param SessionInterface $session
     * @param CartRepositoryInterface $repository
     * @param string $key
     */
    public function __construct(SessionInterface $session, CartRepositoryInterface $repository, $key = self::KEY)
    {
        $this->session = $session;
        $this->repository = $repository;
        $this->key = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function setCart(CartInterface $cart)
    {
        $this->cart = $cart;
        $this->session->set($this->key, $cart->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function clearCart()
    {
        $this->cart = null;
        $this->session->set($this->key, null);
    }

    /**
     * {@inheritdoc}
     */
    public function hasCart()
    {
        if (null !== $this->cart) {
            return true;
        }

        if (null !== $cartId = $this->session->get($this->key, null)) {
            $cart = $this->repository->findOneById($cartId);
            if (null !== $cart) {
                $this->setCart($cart);
                return true;
            } else {
                $this->clearCart();
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getCart()
    {
        if (!$this->hasCart()) {
            if (null === $this->cart) {
                $this->newCart();
            }
        }

        return $this->cart;
    }

    /**
     * Creates a new cart.
     *
     * @return CartInterface
     */
    private function newCart()
    {
        $this->clearCart();

        /** @noinspection PhpParamsInspection */
        $this->setCart($this->repository->createNew());

        return $this->cart;
    }
}
