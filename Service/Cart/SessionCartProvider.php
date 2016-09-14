<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Cart;

use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Provider\AbstractCartProvider;
use Ekyna\Component\Commerce\Cart\Provider\CartProviderInterface;
use Ekyna\Component\Commerce\Cart\Repository\CartRepositoryInterface;
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
     * @inheritdoc
     */
    public function hasCart()
    {
        if (parent::hasCart()) {
            return true;
        }

        if (0 < $id = intval($this->session->get($this->key, 0))) {
            if (null !== $cart = $this->cartRepository->findOneById($id)) {
                $this->setCart($cart);
                return true;
            } else {
                $this->clearCart();
            }
        }

        return false;
    }
}
