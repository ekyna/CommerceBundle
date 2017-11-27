<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Cart;

use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleItemConfigureType;
use Ekyna\Bundle\CommerceBundle\Service\SaleHelper;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Provider\CartProviderInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
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
     * @var \Ekyna\Bundle\CommerceBundle\Service\SaleHelper
     */
    protected $saleHelper;

    /**
     * @var CartProviderInterface
     */
    protected $cartProvider;

    /**
     * @var SubjectHelperInterface
     */
    private $subjectHelper;

    /**
     * @var string
     */
    private $cartItemClass;


    /**
     * Constructor.
     *
     * @param SaleHelper             $saleHelper
     * @param CartProviderInterface  $cartProvider
     * @param SubjectHelperInterface $subjectHelper
     * @param string                 $cartItemClass
     */
    public function __construct(
        SaleHelper $saleHelper,
        CartProviderInterface $cartProvider,
        SubjectHelperInterface $subjectHelper,
        $cartItemClass
    ) {
        $this->saleHelper = $saleHelper;
        $this->cartProvider = $cartProvider;
        $this->subjectHelper = $subjectHelper;
        $this->cartItemClass = $cartItemClass;
    }

    /**
     * Returns the sale helper.
     *
     * @return \Ekyna\Bundle\CommerceBundle\Service\SaleHelper
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
        if (!isset($options['taxes_view'])) {
            $options['taxes_view'] = false;
        }

        return $this->saleHelper->buildView($cart, $options);
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
        $item = new $this->cartItemClass; // TODO Use sale factory (create methods to use interface: SaleInterface, etc)

        $this->subjectHelper->assign($item, $subject);

        // Set cart if available for taxes resolution TODO wtf ?
        if ($this->cartProvider->hasCart()) {
            $item->setSale($this->cartProvider->getCart());
        }

        $options = array_merge_recursive([
            'attr' => [
                'class' => 'form-horizontal',
            ],
        ], $options);

        return $this
            ->saleHelper
            ->getFormFactory()
            ->create(SaleItemConfigureType::class, $item, $options);
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

        if ($form->isSubmitted() && $form->isValid()) {
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
