<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Cart;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use Ekyna\Bundle\CommerceBundle\Event\CheckoutEvent;
use Ekyna\Bundle\CommerceBundle\Form\Type\Checkout\ShipmentType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleTransformType;
use Ekyna\Bundle\CommerceBundle\Service\Payment\CheckoutManager;
use Ekyna\Bundle\CommerceBundle\Service\Payment\PaymentHelper;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\SaleStepValidatorInterface;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Common\Transformer\SaleTransformerInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Features;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Quote\Repository\QuoteRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * @var QuoteRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var CheckoutManager
     */
    protected $checkoutManager;

    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var SaleTransformerInterface
     */
    protected $saleTransformer;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;


    /**
     * Constructor.
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param QuoteRepositoryInterface $quoteRepository
     * @param CheckoutManager          $checkoutManager
     * @param PaymentHelper            $paymentHelper
     * @param SaleTransformerInterface $saleTransformer
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        QuoteRepositoryInterface $quoteRepository,
        CheckoutManager $checkoutManager,
        PaymentHelper $paymentHelper,
        SaleTransformerInterface $saleTransformer,
        EventDispatcherInterface $dispatcher
    ) {
        $this->orderRepository = $orderRepository;
        $this->quoteRepository = $quoteRepository;
        $this->checkoutManager = $checkoutManager;
        $this->paymentHelper = $paymentHelper;
        $this->saleTransformer = $saleTransformer;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Cart index action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request): Response
    {
        $parameters = [];

        // Cart
        $parameters['cart'] = $cart = $this->getCart();
        if (null !== $cart) {
            $saleHelper = $this->getSaleHelper();

            $form = $this->createQuantitiesForm($cart);

            if (!$cart->isLocked()) {
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $this->getCartHelper()->getCartProvider()->updateCustomerGroupAndCurrency();

                    $saleHelper->recalculate($cart);

                    $this->saveCart();
                }
            }

            $view = $this->getCartHelper()->buildView($cart, ['editable' => true]);

            $view->vars['quantities_form'] = $form->createView();

            if ($this->features->isEnabled(Features::COUPON)) {
                $view->vars['coupon_form'] = $this->createCouponForm($cart)->createView();
            }

            // Default shipment method and price message
            if (null !== $shipmentLine = $view->getShipment()) {
                $shipmentLine->setDesignation(
                    $shipmentLine->getDesignation() .
                    '&nbsp;<sup class="text-danger">&starf;</sup>'
                );
                $view->addMessage($this->translate('ekyna_commerce.checkout.message.shipment_defaults'));
            }

            if ($cart->isLocked()) {
                $view->addAlert($this->translate('ekyna_commerce.checkout.message.unlock', [
                    '{{url}}' => $this->generateUrl('ekyna_commerce_cart_checkout_unlock'),
                ]));
            }

            $parameters['view'] = $view;
        }

        $parameters['controls'] = $this->buildCartControls($cart);

        if ($request->isXmlHttpRequest()) {
            return $this->render('@EkynaCommerce/Cart/response.xml.twig', $parameters);
        }

        if (null !== $cart) {
            $this->dispatcher->dispatch(CheckoutEvent::EVENT_INIT, new CheckoutEvent($cart));
        }

        return $this->render('@EkynaCommerce/Cart/Checkout/index.html.twig', $parameters);
    }

    /**
     * Quote transformation action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function quote(Request $request): Response
    {
        if (null === $cart = $this->getCart()) {
            return $this->redirect($this->generateUrl('ekyna_commerce_cart_checkout_index'));
        }

        if (null === $customer = $cart->getCustomer()) {
            throw new AccessDeniedHttpException();
        }
        if (!$customer->getCustomerGroup()->isQuoteAllowed()) {
            throw new AccessDeniedHttpException();
        }

        $cancelPath = $this->generateUrl('ekyna_commerce_cart_checkout_index');

        if (!$this->stepValidator->validate($cart, SaleStepValidatorInterface::SHIPMENT_STEP)) {
            $this->violationToFlashes($this->stepValidator->getViolationList(), $request);

            return $this->redirect($this->generateUrl('ekyna_commerce_cart_checkout_index'));
        }

        $form = $this
            ->getFormFactory()
            ->create(SaleTransformType::class, $cart, [
                'action'  => $this->generateUrl('ekyna_commerce_cart_checkout_quote'),
                'method'  => 'POST',
                'message' => 'ekyna_commerce.checkout.message.quote_confirm',
            ])
            ->add('actions', FormActionsType::class, [
                'buttons' => [
                    'remove' => [
                        'type'    => Type\SubmitType::class,
                        'options' => [
                            'button_class' => 'success',
                            'label'        => 'ekyna_core.button.transform',
                            'attr'         => ['icon' => 'ok'],
                        ],
                    ],
                    'cancel' => [
                        'type'    => Type\ButtonType::class,
                        'options' => [
                            'label'        => 'ekyna_core.button.cancel',
                            'button_class' => 'default',
                            'as_link'      => true,
                            'attr'         => [
                                'class' => 'form-cancel-btn',
                                'icon'  => 'remove',
                                'href'  => $cancelPath,
                            ],
                        ],
                    ],
                ],
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // New quote
            $quote = $this->quoteRepository->createNew();
            // Initialize transformation
            $this->saleTransformer->initialize($cart, $quote);
            // Transform
            if (null === $event = $this->saleTransformer->transform()) {
                // Redirect to quote details
                return $this->redirect($this->generateUrl('ekyna_commerce_account_quote_show', [
                    'number' => $quote->getNumber(),
                ]));
            }

            // Display event's flash messages
            if (null !== $session = $request->getSession()) {
                /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
                $event->toFlashes($session->getFlashBag());
            }
        }

        return $this->render('@EkynaCommerce/Cart/Checkout/quote.html.twig', [
            'cart' => $cart,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Shipment action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function shipment(Request $request): Response
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

        $this->dispatcher->dispatch(CheckoutEvent::EVENT_SHIPMENT_STEP, new CheckoutEvent($cart));

        $view = $form->createView();

        return $this->render('@EkynaCommerce/Cart/Checkout/shipment.html.twig', [
            'cart' => $cart,
            'form' => $view,
        ]);
    }

    /**
     * Payment action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function payment(Request $request): Response
    {
        if (null === $cart = $this->getCart()) {
            return $this->redirect($this->generateUrl('ekyna_commerce_cart_checkout_index'));
        }

        if (!$this->stepValidator->validate($cart, SaleStepValidatorInterface::PAYMENT_STEP)) {
            $this->violationToFlashes($this->stepValidator->getViolationList(), $request);

            return $this->redirect($this->generateUrl('ekyna_commerce_cart_checkout_shipment'));
        }

        $this->checkoutManager->initialize($cart, $this->generateUrl('ekyna_commerce_cart_checkout_payment'));

        if (null !== $payment = $this->checkoutManager->handleRequest($request)) {
            $cart->addPayment($payment);
            $this->saveCart();

            $statusUrl = $this->generateUrl(
                'ekyna_commerce_cart_checkout_status',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            return $this->paymentHelper->capture($payment, $statusUrl);
        }

        $this->dispatcher->dispatch(CheckoutEvent::EVENT_PAYMENT_STEP, new CheckoutEvent($cart));

        return $this->render('@EkynaCommerce/Cart/Checkout/payment.html.twig', [
            'cart'  => $cart,
            'forms' => $this->checkoutManager->getFormsViews(),
        ]);
    }

    /**
     * Unlock action.
     *
     * @return Response
     */
    public function unlock(): Response
    {
        if (null === $cart = $this->getCart()) {
            return $this->redirect($this->generateUrl('ekyna_commerce_cart_checkout_index'));
        }

        $statusUrl = $this->generateUrl(
            'ekyna_commerce_cart_checkout_status',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        foreach ($cart->getPayments() as $payment) {
            $method = $payment->getMethod();
            if ($method->isManual() || $method->isOutstanding() || $method->isCredit()) {
                continue;
            }

            if (PaymentStates::STATE_NEW === $payment->getState()) {
                return $this->paymentHelper->cancel($payment, $statusUrl);
            }
        }

        return $this->redirect($this->generateUrl('ekyna_commerce_cart_checkout_index'));
    }

    /**
     * Status action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function status(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            throw new NotFoundHttpException("XHR is not supported.");
        }

        if (null === $payment = $this->paymentHelper->status($request)) {
            // Cart has been deleted (fraud)
            return $this->redirect($this->generateUrl('ekyna_commerce_cart_checkout_index'));
        }

        $sale = $payment->getSale();

        if ($sale instanceof OrderInterface) {
            return $this->redirect($this->generateUrl('ekyna_commerce_cart_checkout_confirmation', [
                'orderKey' => $sale->getKey(),
            ]));
        }

        if (!$sale instanceof CartInterface) {
            throw new InvalidArgumentException("Expected instance of " . CartInterface::class);
        }

        // Else go back to payments
        return $this->redirect($this->generateUrl('ekyna_commerce_cart_checkout_payment'));
    }

    /**
     * Confirmation action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function confirmation(Request $request): Response
    {
        $order = $this->orderRepository->findOneByKey($request->attributes->get('orderKey'));

        if (null === $order) {
            throw new NotFoundHttpException('Order not found.');
        }

        if (!is_null($orderCustomer = $order->getCustomer())) {
            $currentCustomer = $this->getCustomer();
            if ($currentCustomer && $currentCustomer->hasParent()) {
                $currentCustomer = $currentCustomer->getParent();
            }

            if ($orderCustomer !== $currentCustomer) {
                throw new AccessDeniedHttpException();
            }
        }

        $this->dispatcher->dispatch(CheckoutEvent::EVENT_CONFIRMATION, new CheckoutEvent($order));

        return $this->render('@EkynaCommerce/Cart/Checkout/confirmation.html.twig', [
            'order' => $order,
        ]);
    }
}
