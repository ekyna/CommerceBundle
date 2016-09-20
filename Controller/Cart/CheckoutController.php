<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Cart;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class CheckoutController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Cart
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CheckoutController extends AbstractController
{
    /**
     * Cart index action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        if ($request->isXmlHttpRequest() && ($request->getMethod() != 'POST')) {
            throw new NotFoundHttpException();
        }

        $saleHelper = $this->getSaleHelper();

        $view = null;
        if (null !== $cart = $this->getCart()) {
            $formOptions = [
                'method' => 'post',
                'action' => $this->generateUrl('ekyna_commerce_cart_checkout_index'),
            ];

            $form = $saleHelper->createQuantitiesForm($cart, $formOptions);
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->getCartHelper()->getCartProvider()->saveCart();
            }

            $view = $this->getCartHelper()->buildView($cart, ['editable' => true]);
            $view->vars['form'] = $form->createView();

            if ($request->isXmlHttpRequest()) {
                return $this->render('EkynaCommerceBundle:Cart:response.xml.twig', [
                    'cart_view' => $view,
                ]);
            }
        }

        return $this->render('EkynaCommerceBundle:Cart/Checkout:index.html.twig', [
            'base_template' => $this->getBaseTemplate(),
            'cart_view'     => $view,
        ]);
    }

    /**
     * Returns the base template.
     * The base template must have a 'content' block in which the cart view will be rendered.
     *
     * @return string
     */
    protected function getBaseTemplate()
    {
        return 'AppBundle::base.html.twig'; // TODO parameter
    }
}
