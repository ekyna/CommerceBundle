<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Security;

use Ekyna\Component\Commerce\Cart\Provider\CartProviderInterface;
use Ekyna\Component\Commerce\Customer\Provider\CustomerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

/**
 * Class LogoutHandler
 * @package Ekyna\Bundle\CommerceBundle\Service\Security
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class LogoutHandler implements LogoutHandlerInterface
{
    /**
     * @var CartProviderInterface
     */
    private $cartProvider;

    /**
     * @var CustomerProviderInterface
     */
    private $customerProvider;

    /**
     * Constructor.
     *
     * @param CartProviderInterface     $cartProvider
     * @param CustomerProviderInterface $customerProvider
     */
    public function __construct(CartProviderInterface $cartProvider, CustomerProviderInterface $customerProvider)
    {
        $this->cartProvider = $cartProvider;
        $this->customerProvider = $customerProvider;
    }

    /**
     * @inheritdoc
     */
    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        if ($this->cartProvider->hasCart() && $this->customerProvider->hasCustomer()) {
            $cart = $this->cartProvider->getCart();
            $customer = $this->customerProvider->getCustomer();

            if ($cart->getCustomer() === $customer) {
                $this
                    ->cartProvider
                    ->clearInformation()
                    ->saveCart();
            }
        }
    }
}
