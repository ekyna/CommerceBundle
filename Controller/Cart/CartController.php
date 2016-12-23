<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Cart;

use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleItemSubjectType;
use Ekyna\Bundle\CoreBundle\Modal;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class CartController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Cart
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartController extends AbstractController
{
    /**
     * @var Modal\Renderer
     */
    private $modalRenderer;

    /**
     * Constructor.
     *
     * @param Modal\Renderer $modalRenderer
     */
    public function __construct(Modal\Renderer $modalRenderer)
    {
        $this->modalRenderer = $modalRenderer;
    }

    /**
     * (Re)configures the item.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function configureItemAction(Request $request)
    {
        if (null === $cart = $this->getCart()) {
            throw new NotFoundHttpException('Cart not found.');
        }

        $itemId = intval($request->attributes->get('itemId'));
        if (0 >= $itemId) {
            throw new NotFoundHttpException('Unexpected item identifier.');
        }

        $saleHelper = $this->getSaleHelper();

        if (null === $item = $saleHelper->findItemById($cart, $itemId)) {
            throw new NotFoundHttpException('Item not found.');
        }
        if (!$item->isConfigurable()) {
            throw new NotFoundHttpException('Item is not configurable.');
        }
        if ($item->isImmutable()) {
            throw new NotFoundHttpException('Item is immutable.');
        }

        // TODO if not XHR, redirect to product detail page with itemID for configuration

        $form = $this
            ->getFormFactory()
            ->create(SaleItemSubjectType::class, $item, [
                'method' => 'post',
                'action' => $this->generateUrl('ekyna_commerce_cart_configure_item', [
                    'itemId' => $item->getId(),
                ]),
                'attr'   => [
                    'class' => 'form-horizontal',
                ],
            ]);

        $form->handleRequest($request);
        if ($form->isValid()) {
            // TODO use operator to update item (cart will be automatically saved)
            $this->getCartHelper()->getCartProvider()->saveCart();

            return $this->buildXhrCartViewResponse();
        }

        // TODO title trans
        $modal = $this->createModal('Configurer l\'article', $form->createView());
        $modal->setVars([
            'form_template' => 'EkynaCommerceBundle:Form:sale_item_subject_form.html.twig',
        ]);

        return $this->modalRenderer->render($modal);
    }

    /**
     * Removes the item.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeItemAction(Request $request)
    {
        if (null === $cart = $this->getCart()) {
            throw new NotFoundHttpException('Cart not found.');
        }

        $itemId = intval($request->attributes->get('itemId'));
        if (0 < $itemId) {
            // TODO use operator to delete item (cart will be automatically saved)
            if ($this->getSaleHelper()->removeItemById($cart, $itemId)) {
                if ($cart->hasItems()) {
                    $this->getCartHelper()->getCartProvider()->saveCart();
                } else {
                    $this->getCartHelper()->getCartProvider()->clearCart();
                }
            } else {
                // TODO Warn about immutable item ?
            }
        } else {
            throw new NotFoundHttpException('Unexpected item identifier.');
        }

        if ($request->isXmlHttpRequest()) {
            return $this->buildXhrCartViewResponse();
        }

        return $this->redirect($this->generateUrl('ekyna_commerce_cart_checkout_index'));
    }

    public function removeItemAdjustmentAction(Request $request)
    {
        throw new \Exception('Not yet implemented.'); // TODO
    }

    public function removeAdjustmentAction(Request $request)
    {
        throw new \Exception('Not yet implemented.'); // TODO
    }

    /**
     * Edit invoice address action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function invoiceAddressAction(Request $request)
    {
        if (null === $cart = $this->getCart()) {
            throw new NotFoundHttpException('Cart not found.');
        }

        $form = $this
            ->getFormFactory()
            ->create(SaleItemSubjectType::class, $cart, [
                'method' => 'post',
                'action' => $this->generateUrl('ekyna_commerce_cart_invoice_address', [
                    'itemId' => $item->getId(),
                ]),
                'attr'   => [
                    'class' => 'form-horizontal',
                ],
            ]);

        $form->handleRequest($request);
        if ($form->isValid()) {
            // TODO use operator to update item (cart will be automatically saved)
            $this->getCartHelper()->getCartProvider()->saveCart();

            return $this->buildXhrCartViewResponse();
        }

        // TODO title trans
        $modal = $this->createModal('Configurer l\'article', $form->createView());
        $modal->setVars([
            'form_template' => 'EkynaCommerceBundle:Form:sale_item_subject_form.html.twig',
        ]);

        return $this->modalRenderer->render($modal);
    }

    /**
     * Edit delivery address action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deliveryAddressAction(Request $request)
    {
        if (null === $cart = $this->getCart()) {
            throw new NotFoundHttpException('Cart not found.');
        }

    }

    /**
     * Creates a modal.
     *
     * @param string $title
     * @param mixed  $content
     * @param array  $buttons
     *
     * @return Modal\Modal
     */
    protected function createModal($title, $content = null, $buttons = [])
    {
        if (empty($buttons)) {
            $buttons = [
                [
                    'id'       => 'submit',
                    'label'    => 'ekyna_core.button.save',
                    'icon'     => 'glyphicon glyphicon-ok',
                    'cssClass' => 'btn-success',
                    'autospin' => true,
                ],
                [
                    'id'       => 'close',
                    'label'    => 'ekyna_core.button.cancel',
                    'icon'     => 'glyphicon glyphicon-remove',
                    'cssClass' => 'btn-default',
                ],
            ];
        }

        return new Modal\Modal($title, $content, $buttons);
    }

    /**
     * Returns the XHR cart view response.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function buildXhrCartViewResponse()
    {
        $view = null;
        if (null !== $cart = $this->getCart()) {
            $saleHelper = $this->getSaleHelper();

            $form = $saleHelper->createQuantitiesForm($cart, [
                'method' => 'post',
                'action' => $this->generateUrl('ekyna_commerce_cart_checkout_index'),
            ]);

            $view = $this->getCartHelper()->buildView($cart, ['editable' => true]);
            $view->vars['form'] = $form->createView();
        }

        $response = $this->render('EkynaCommerceBundle:Cart:response.xml.twig', [
            'cart_view' => $view,
        ]);

        $response->headers->set('Content-type', 'application/xml');

        return $response;
    }
}
