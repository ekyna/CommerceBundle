<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Cart;

use Ekyna\Bundle\CommerceBundle\Form\Type\Cart\CartAddressType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleAddressType;
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
        $parameters = [];

        // Customer
        if (null !== $customer = $this->getCustomer()) {
            $parameters['customer'] = $customer;
        }

        // Cart
        $cart = $this->getCart();
        if (null !== $cart) {
            $parameters['cart'] = $cart;

            $saleHelper = $this->getSaleHelper();

            $saleForm = $saleHelper->createQuantitiesForm($cart, [
                'method' => 'post',
                'action' => $this->generateUrl('ekyna_commerce_cart_checkout_index'),
            ]);

            $saleForm->handleRequest($request);
            if ($saleForm->isSubmitted()) {
                if ($saleForm->isValid()) {
                    $saleHelper->recalculate($cart);
                    $this->getCartHelper()->getCartProvider()->saveCart();
                }
            }

            $view = $this->getCartHelper()->buildView($cart, ['editable' => true]);
            $view->vars['form'] = $saleForm->createView();

            $parameters['view'] = $view;
        }

        if ($request->isXmlHttpRequest()) {
            return $this->render('EkynaCommerceBundle:Cart:response.xml.twig', $parameters);
        }

        return $this->render('EkynaCommerceBundle:Cart/Checkout:index.html.twig', $parameters);
    }

    /**
     * Information action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function informationAction(Request $request)
    {
        $customer = $this->getCustomer();

        if (null === $customer) {
            // TODO Set form login redirection
            $request->getSession()->set('_ekyna.login_success.target_path', 'ekyna_commerce_cart_checkout_information');

            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }


        return $this->render('EkynaCommerceBundle:Cart/Checkout:information.html.twig', [
            'customer' => $customer,
        ]);
    }

    /**
     * Shipment action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function shipmentAction(Request $request)
    {
        return $this->render('EkynaCommerceBundle:Cart/Checkout:shipment.html.twig', [
        ]);
    }

    /**
     * Payment action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function paymentAction(Request $request)
    {
        return $this->render('EkynaCommerceBundle:Cart/Checkout:payment.html.twig', [
        ]);
    }

    /**
     * Confirmation action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confirmationAction(Request $request)
    {
        return $this->render('EkynaCommerceBundle:Cart/Checkout:confirmation.html.twig', [
        ]);
    }
}
