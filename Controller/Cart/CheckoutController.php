<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Cart;

use Ekyna\Bundle\CommerceBundle\Form\Type\Checkout\ShipmentType;
use Ekyna\Bundle\CommerceBundle\Service\Checkout\PaymentManager;
use Ekyna\Bundle\CommerceBundle\Service\Common\SaleTransformerInterface;
use Ekyna\Bundle\CommerceBundle\Service\Payment\PaymentHelper;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\SaleStepValidatorInterface;
use Ekyna\Component\Commerce\Cart\Model\CartPaymentInterface;
use Ekyna\Component\Commerce\Cart\Model\CartStates;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class CheckoutController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Cart
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CheckoutController extends AbstractController
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var PaymentManager
     */
    protected $paymentCheckout;

    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var SaleTransformerInterface
     */
    protected $saleTransformer;


    /**
     * Constructor.
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param PaymentManager           $paymentCheckout
     * @param PaymentHelper            $paymentHelper
     * @param SaleTransformerInterface $saleTransformer
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        PaymentManager $paymentCheckout,
        PaymentHelper $paymentHelper,
        SaleTransformerInterface $saleTransformer
    ) {
        $this->orderRepository = $orderRepository;
        $this->paymentCheckout = $paymentCheckout;
        $this->paymentHelper = $paymentHelper;
        $this->saleTransformer = $saleTransformer;
    }

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

        // Cart
        $parameters['cart'] = $cart = $this->getCart();
        if (null !== $cart) {
            $saleHelper = $this->getSaleHelper();

            $saleForm = $saleHelper->createQuantitiesForm($cart, [
                'method' => 'post',
                'action' => $this->generateUrl('ekyna_commerce_cart_checkout_index'),
            ]);

            $saleForm->handleRequest($request);

            if ($saleForm->isSubmitted() && $saleForm->isValid()) {
                $saleHelper->recalculate($cart);

                $this->saveCart();
            }

            $view = $this->getCartHelper()->buildView($cart, ['editable' => true]);
            $view->vars['form'] = $saleForm->createView();

            $parameters['view'] = $view;
        }

        $parameters['controls'] = $this->buildCartControls($cart);

        if ($request->isXmlHttpRequest()) {
            return $this->render('EkynaCommerceBundle:Cart:response.xml.twig', $parameters);
        }

        return $this->render('EkynaCommerceBundle:Cart/Checkout:index.html.twig', $parameters);
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
        if (null === $cart = $this->getCart()) {
            return $this->redirect($this->generateUrl('ekyna_commerce_cart_checkout_index'));
        }

        if (!$this->stepValidator->validate($cart, SaleStepValidatorInterface::SHIPMENT_STEP)) {
            $this->violationToFlashes($this->stepValidator->getViolationList(), $request);

            return $this->redirect($this->generateUrl('ekyna_commerce_cart_checkout_index'));
        }

        $form = $this
            ->getFormFactory()
            ->create(ShipmentType::class, $cart, [
                'action' => $this->generateUrl('ekyna_commerce_cart_checkout_shipment'),
                'method' => 'POST',
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->saveCart();

            return $this->redirect($this->generateUrl('ekyna_commerce_cart_checkout_payment'));
        }

        return $this->render('EkynaCommerceBundle:Cart/Checkout:shipment.html.twig', [
            'cart' => $cart,
            'form' => $form->createView(),
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
        if (null === $cart = $this->getCart()) {
            return $this->redirect($this->generateUrl('ekyna_commerce_cart_checkout_index'));
        }

        if (!$this->stepValidator->validate($cart, SaleStepValidatorInterface::PAYMENT_STEP)) {
            $this->violationToFlashes($this->stepValidator->getViolationList(), $request);

            return $this->redirect($this->generateUrl('ekyna_commerce_cart_checkout_index'));
        }

        $this->paymentCheckout->initialize($cart, $this->generateUrl('ekyna_commerce_cart_checkout_payment'));

        if (null !== $payment = $this->paymentCheckout->handleRequest($request)) {
            $cart->addPayment($payment);
            $this->saveCart();

            $statusUrl = $this->generateUrl(
                'ekyna_commerce_cart_checkout_status',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            return $this->paymentHelper->capture($payment, $statusUrl);
        }

        return $this->render('EkynaCommerceBundle:Cart/Checkout:payment.html.twig', [
            'cart'  => $cart,
            'forms' => $this->paymentCheckout->getFormsViews(),
        ]);
    }

    /**
     * Status action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function statusAction(Request $request)
    {
        $payment = $this->paymentHelper->status($request);

        if (!$payment instanceof CartPaymentInterface) {
            throw new InvalidArgumentException("Expected instance of " . CartPaymentInterface::class);
        }

        // TODO Check that payment cart is the same as user (session) one
        // Problem : token has been invalidated
        $cart = $this->getCart();

        // If cart is completed, transforms and redirect to order confirmation
        if (in_array($cart->getState(), [CartStates::STATE_ACCEPTED])) {
            $order = $this->saleTransformer->transformCartToOrder($cart);

            return $this->redirect($this->generateUrl('ekyna_commerce_cart_checkout_confirmation', [
                'orderKey' => $order->getKey(),
            ]));
        }

        // Else go back to payments
        return $this->redirect($this->generateUrl('ekyna_commerce_cart_checkout_payment'));
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
        $order = $this->orderRepository->findOneByKey($request->attributes->get('orderKey'));

        if (null === $order) {
            throw new NotFoundHttpException('Order not found.');
        }

        $orderCustomer = $order->getCustomer();
        $currentCustomer = $this->getCustomer();

        if ($orderCustomer && $currentCustomer && $orderCustomer !== $currentCustomer) {
            throw new AccessDeniedHttpException();
        }

        return $this->render('EkynaCommerceBundle:Cart/Checkout:confirmation.html.twig', [
            'order' => $order,
        ]);
    }
}
