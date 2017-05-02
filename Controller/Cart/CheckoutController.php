<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Cart;

use Ekyna\Bundle\CommerceBundle\Form\Type\Checkout\ShipmentType;
use Ekyna\Bundle\CommerceBundle\Service\Checkout\PaymentManager;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\SaleStepValidatorInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Class CheckoutController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Cart
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CheckoutController extends AbstractController
{
    /**
     * @var SaleStepValidatorInterface
     */
    protected $stepValidator;

    /**
     * @var PaymentManager
     */
    protected $paymentCheckout;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;


    /**
     * Constructor.
     *
     * @param SaleStepValidatorInterface $stepValidator
     * @param OrderRepositoryInterface   $orderRepository
     * @param PaymentManager             $paymentCheckout
     */
    public function __construct(
        SaleStepValidatorInterface $stepValidator,
        OrderRepositoryInterface $orderRepository,
        PaymentManager $paymentCheckout
    ) {
        $this->stepValidator = $stepValidator;
        $this->orderRepository = $orderRepository;
        $this->paymentCheckout = $paymentCheckout;
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

        // Customer
        if (null !== $customer = $this->getCustomer()) {
            $parameters['customer'] = $customer;
        }

        // Cart
        $parameters['cart'] = $cart = $this->getCart();
        if (null !== $cart) {
            $saleHelper = $this->getSaleHelper();

            $saleForm = $saleHelper->createQuantitiesForm($cart, [
                'method'            => 'post',
                'action'            => $this->generateUrl('ekyna_commerce_cart_checkout_index'),
                'validation_groups' => ['checkout'], // TODO
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

        if ($request->isXmlHttpRequest()) {
            return $this->render('EkynaCommerceBundle:Cart:response.xml.twig', $parameters);
        }

        return $this->render('EkynaCommerceBundle:Cart/Checkout:index.html.twig', $parameters);
    }

    /**
     * Information action.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function informationAction()
    {
        throw new AccessDeniedHttpException('Deprecated');

        /*$customer = $this->getCustomer();

        if (null === $customer) {
            // TODO Set form login redirection
            $request->getSession()->set('_ekyna.login_success.target_path', 'ekyna_commerce_cart_checkout_information');

            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        return $this->render('EkynaCommerceBundle:Cart/Checkout:information.html.twig', [
            'customer' => $customer,
        ]);*/
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

        $this->paymentCheckout->initialize($cart);

        if (null !== $payment = $this->paymentCheckout->handleRequest($request)) {
            $cart->addPayment($payment);
            $this->saveCart();

            return $this->redirect($this->generateUrl('ekyna_commerce_payment_cart_capture', [
                'key' => $payment->getKey(),
            ]));
        }

        return $this->render('EkynaCommerceBundle:Cart/Checkout:payment.html.twig', [
            'cart'  => $cart,
            'forms' => $this->paymentCheckout->getFormsViews(),
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

    /**
     * Transforms the constraint violation list to session flashes.
     *
     * @param ConstraintViolationListInterface $list
     * @param Request                          $request
     */
    protected function violationToFlashes(ConstraintViolationListInterface $list, Request $request)
    {
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $request->getSession();
        $flashes = $session->getFlashBag();

        $messages = [];

        /** @var \Symfony\Component\Validator\ConstraintViolationInterface $violation */
        foreach ($list as $violation) {
            $messages[] = $violation->getMessage();
        }

        if (!empty($messages)) {
            $flashes->add('danger', implode('<br>', $messages));
        }
    }
}
