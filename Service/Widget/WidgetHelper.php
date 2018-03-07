<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Widget;

use Ekyna\Bundle\UserBundle\Service\Provider\UserProviderInterface;
use Ekyna\Component\Commerce\Cart\Provider\CartProviderInterface;
use Ekyna\Component\Commerce\Common\Util\Formatter;
use Ekyna\Component\Commerce\Customer\Provider\CustomerProviderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class WidgetHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Widget
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class WidgetHelper
{
    /**
     * @var CustomerProviderInterface
     */
    private $customerProvider;

    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var CartProviderInterface
     */
    private $cartProvider;

    /**
     * @var Formatter
     */
    private $formatter;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var TranslatorInterface
     */
    private $translator;


    /**
     * Constructor.
     *
     * @param CustomerProviderInterface $customerProvider
     * @param UserProviderInterface     $userProvider
     * @param CartProviderInterface     $cartProvider
     * @param Formatter                 $formatter
     * @param UrlGeneratorInterface     $urlGenerator
     * @param TranslatorInterface       $translator
     */
    public function __construct(
        CustomerProviderInterface $customerProvider,
        UserProviderInterface     $userProvider,
        CartProviderInterface $cartProvider,
        Formatter $formatter,
        UrlGeneratorInterface $urlGenerator,
        TranslatorInterface $translator
    ) {
        $this->customerProvider = $customerProvider;
        $this->userProvider = $userProvider;
        $this->cartProvider = $cartProvider;
        $this->formatter = $formatter;
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
    }

    /**
     * Returns the customer.
     *
     * @return \Ekyna\Component\Commerce\Customer\Model\CustomerInterface|null
     */
    public function getCustomer()
    {
        return $this->customerProvider->getCustomer();
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
        return $this->cartProvider->getCart();
    }

    /**
     * Returns the customer widget data.
     *
     * @return array
     */
    public function getCustomerWidgetData()
    {
        $label = $this->translator->trans('ekyna_commerce.account.widget.title');

        $data = [
            'id'     => 'customer-widget',
            'href'   => $this->urlGenerator->generate('ekyna_user_account_index'),
            'title'  => $label,
            'label'  => $label,
            'icon'   => 'glyphicon glyphicon-user',
            'config' => [
                'widget'   => 'ekyna_commerce_widget_customer_widget',
                'dropdown' => 'ekyna_commerce_widget_customer_dropdown',
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
        $label = $this->translator->trans('ekyna_commerce.cart.widget.title');

        $data = [
            'id'     => 'cart-widget',
            'href'   => $this->urlGenerator->generate('ekyna_commerce_cart_checkout_index'),
            'title'  => $label,
            'label'  => $label,
            'icon'   => 'glyphicon glyphicon-shopping-cart',
            'config' => [
                'widget'   => 'ekyna_commerce_widget_cart_widget',
                'dropdown' => 'ekyna_commerce_widget_cart_dropdown',
            ],
        ];

        $cart = $this->getCart();
        if ((null !== $cart) && $cart->hasItems()) {
            $count = $cart->getItems()->count();
            $count = $this->translator->transChoice('ekyna_commerce.cart.widget.items', $count, ['%count%' => $count]);
            $total = $this->formatter->currency($cart->getGrandTotal());
            $data['label'] = $count . ' <strong>' . $total . '</strong>';
        }

        return $data;
    }
}