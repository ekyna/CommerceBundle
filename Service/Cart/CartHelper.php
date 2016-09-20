<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Cart;

use Ekyna\Bundle\CommerceBundle\Form\Type\SaleItemSubjectType;
use Ekyna\Bundle\CommerceBundle\Helper\SaleHelper;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Provider\CartProviderInterface;
use Ekyna\Component\Commerce\Common\View\ViewVarsBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CartHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Cart
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartHelper
{
    /**
     * @var SaleHelper
     */
    protected $saleHelper;

    /**
     * @var CartProviderInterface
     */
    protected $cartProvider;

    /**
     * @var ViewVarsBuilderInterface
     */
    protected $viewVarsBuilder;

    /**
     * @var string
     */
    protected $cartItemClass;


    /**
     * Constructor.
     *
     * @param SaleHelper               $saleHelper
     * @param CartProviderInterface    $cartProvider
     * @param ViewVarsBuilderInterface $viewVarsBuilder
     * @param string                   $cartItemClass
     */
    public function __construct(
        SaleHelper $saleHelper,
        CartProviderInterface $cartProvider,
        ViewVarsBuilderInterface $viewVarsBuilder,
        $cartItemClass
    ) {
        $this->saleHelper = $saleHelper;
        $this->cartProvider = $cartProvider;
        $this->viewVarsBuilder = $viewVarsBuilder;
        $this->cartItemClass = $cartItemClass;
    }

    /**
     * Returns the sale helper.
     *
     * @return SaleHelper
     */
    public function getSaleHelper()
    {
        return $this->saleHelper;
    }

    /**
     * Returns the cart provider.
     *
     * @return CartProviderInterface
     */
    public function getCartProvider()
    {
        return $this->cartProvider;
    }

    /**
     * Builds the cart view.
     *
     * @param CartInterface $cart
     * @param array         $options
     *
     * @return \Ekyna\Component\Commerce\Common\View\SaleView
     */
    public function buildView(CartInterface $cart, array $options = [])
    {
        return $this->saleHelper->buildView($cart, array_replace([
            'vars_builder' => $this->viewVarsBuilder,
        ], $options));
    }

    /**
     * Creates the 'add subject to cart' form.
     *
     * @param mixed $subject
     * @param array $options
     *
     * @return FormInterface
     */
    public function createAddSubjectToCartForm($subject, array $options = [])
    {
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleItemInterface $item */
        $item = new $this->cartItemClass;
        $item->setSubject($subject);

        // Set cart if available for taxes resolution
        if ($this->cartProvider->hasCart()) {
            $item->setSale($this->cartProvider->getCart());
        }

        $options = array_merge_recursive([
            'attr' => [
                'class' => 'form-horizontal',
            ],
        ], $options);

        return $this
            ->saleHelper->getFormFactory()
            ->create(SaleItemSubjectType::class, $item, $options);
    }

    /**
     * Handles the 'add subject to cart' form submission.
     *
     * @param FormInterface $form
     * @param Request       $request
     *
     * @return bool
     */
    public function handleAddSubjectToCartForm(FormInterface $form, Request $request)
    {
        $form->handleRequest($request);
        if ($form->isValid()) {
            $item = $form->getData();

            if ($this->cartProvider->hasCart()) {
                $cart = $this->cartProvider->getCart();
            } else {
                $cart = $this->cartProvider->createCart();
            }

            $cart->addItem($item);

            // TODO Validate and add violations to form errors ?

            $this->cartProvider->saveCart();

            return true;
        }

        return false;
    }
}
