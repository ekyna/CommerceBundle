<?php

namespace Ekyna\Bundle\CommerceBundle\Helper;

use Ekyna\Bundle\CommerceBundle\Form\Type\SaleItemSubjectType;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Provider\CartProviderInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Common\View\Action;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CartHelper
 * @package Ekyna\Bundle\CommerceBundle\Helper
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
     * @var string
     */
    protected $cartItemClass;


    /**
     * Constructor.
     *
     * @param SaleHelper            $saleHelper
     * @param CartProviderInterface $cartProvider
     * @param string                $cartItemClass
     */
    public function __construct(
        SaleHelper $saleHelper,
        CartProviderInterface $cartProvider,
        $cartItemClass
    ) {
        $this->saleHelper = $saleHelper;
        $this->cartProvider = $cartProvider;
        $this->cartItemClass = $cartItemClass;
    }

    /**
     * Returns the saleHelper.
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
            'item_vars'       => [$this, 'buildItemViewVars'],
            'adjustment_vars' => [$this, 'buildAdjustmentViewVars'],
        ], $options));
    }

    /**
     * Builds the item view's vars.
     *
     * @param SaleItemInterface $item
     *
     * @return array
     * @internal
     */
    public function buildItemViewVars(SaleItemInterface $item)
    {
        if ($item->isImmutable()) {
            return [];
        }

        $actions = [];

        // Configure action
        if ($item->isConfigurable()) {
            $configurePath = $this->saleHelper->generateUrl('ekyna_commerce_cart_configure_item', [
                'itemId' => $item->getId(),
            ]);
            $actions[] = new Action($configurePath, 'fa fa-cog', [
                'title'      => $this->saleHelper->translate('ekyna_commerce.sale.button.configure_item'),
                'data-sale-modal' => null,
            ]);
        }

        // Remove action
        $removePath = $this->saleHelper->generateUrl('ekyna_commerce_cart_remove_item', [
            'itemId' => $item->getId(),
        ]);
        $actions[] = new Action($removePath, 'fa fa-remove', [
            'title'    => $this->saleHelper->translate('ekyna_commerce.sale.button.remove_item'),
            'confirm'  => $this->saleHelper->translate('ekyna_commerce.sale.confirm.remove_item'),
            'data-sale-xhr' => null,
        ]);

        return [
            'actions' => $actions,
        ];
    }

    /**
     * Builds the adjustment view's vars.
     *
     * @param AdjustmentInterface $adjustment
     *
     * @return array
     * @internal
     */
    public function buildAdjustmentViewVars(AdjustmentInterface $adjustment)
    {
        /* TODO if ($adjustment->isImmutable()) {
            return [];
        }*/

        $actions = [];

        $adjustable = $adjustment->getAdjustable();
        if ($adjustable instanceof SaleInterface) {
            $removePath = $this->saleHelper->generateUrl('ekyna_commerce_cart_remove_adjustment', [
                'adjustmentId' => $adjustment->getId(),
            ]);
        } elseif ($adjustable instanceof SaleItemInterface) {
            $removePath = $this->saleHelper->generateUrl('ekyna_commerce_cart_remove_item_adjustment', [
                'itemId'       => $adjustable->getId(),
                'adjustmentId' => $adjustment->getId(),
            ]);
        } else {
            throw new InvalidArgumentException('Unexpected adjustable.');
        }

        $actions[] = new Action($removePath, 'fa fa-remove', [
            'title'   => $this->saleHelper->translate('ekyna_commerce.sale.button.remove_adjustment'),
            'confirm' => $this->saleHelper->translate('ekyna_commerce.sale.confirm.remove_adjustment'),
            'data-sale-xhr' => null,
        ]);

        return [
            'actions' => $actions,
        ];
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
