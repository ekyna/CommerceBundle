<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Cart;

use Ekyna\Component\Commerce\Cart\Provider\AbstractCartProvider;
use Ekyna\Component\Commerce\Cart\Provider\CartProviderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class SessionCartProvider
 * @package Ekyna\Bundle\CommerceBundle\Service\Cart
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SessionCartProvider extends AbstractCartProvider implements CartProviderInterface
{
    const KEY = 'cart_id';

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var bool
     */
    protected $initialized;


    /**
     * Constructor.
     *
     * @param SessionInterface $session
     * @param string $key
     */
    public function __construct(SessionInterface $session, $key = self::KEY)
    {
        $this->session = $session;
        $this->key = $key;
    }

    /**
     * @inheritdoc
     */
    public function hasCart()
    {
        $this->initialize();

        return parent::hasCart();
    }

    /**
     * @inheritdoc
     */
    public function getCart($create = false)
    {
        $this->initialize();

        return parent::getCart($create);
    }

    /**
     * @inheritdoc
     */
    public function clearCart()
    {
        parent::clearCart();

        $this->session->set($this->key, null);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function saveCart()
    {
        parent::saveCart();

        $this->session->set($this->key, $this->cart->getId());

        return $this;
    }

    /**
     * Initializes the cart regarding to the session data or the current customer.
     */
    protected function initialize()
    {
        if (!$this->initialized) {
            $this->initialized = true;

            // By session id
            if (0 < $id = intval($this->session->get($this->key, 0))) {
                if (null !== $cart = $this->cartRepository->findOneById($id)) {
                    $this->setCart($cart);

                    return;
                }
            }

            // By customer
            if ($this->customerProvider->hasCustomer()) {
                /** @var \Ekyna\Component\Commerce\Cart\Model\CartInterface $cart */
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
