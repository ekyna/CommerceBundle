<?php

namespace Ekyna\Bundle\CommerceBundle\Helper;

use Ekyna\Bundle\CommerceBundle\Form\Type\SaleItemSubjectType;
use Ekyna\Component\Commerce\Cart\Provider\CartProviderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CartHelper
 * @package Ekyna\Bundle\CommerceBundle\Helper
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartHelper
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var CartProviderInterface
     */
    private $cartProvider;

    /**
     * @var string
     */
    private $cartItemClass;


    /**
     * Constructor.
     *
     * @param FormFactoryInterface  $formFactory
     * @param CartProviderInterface $cartProvider
     * @param string                $cartItemClass
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        CartProviderInterface $cartProvider,
        $cartItemClass
    ) {
        $this->formFactory   = $formFactory;
        $this->cartProvider  = $cartProvider;
        $this->cartItemClass = $cartItemClass;
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

        return $this->formFactory->create(SaleItemSubjectType::class, $item, $options);
    }

    /**
     * Handles the 'add subject to cart' form submission.
     *
     * @param FormInterface $form
     * @param Request       $request
     *
     * @return bool|Response
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

            // TODO Validate ?

            $this->cartProvider->saveCart();

            // TODO XHR

            return true;
        }

        return false;
    }
}
