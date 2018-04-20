<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Cart;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Checkout\ShipmentType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleTransformType;
use Ekyna\Bundle\CommerceBundle\Service\Checkout\PaymentManager;
use Ekyna\Bundle\CommerceBundle\Service\Payment\PaymentHelper;
use Ekyna\Component\Commerce\Bridge\Payum\Request\Status;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\SaleStepValidatorInterface;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Transformer\SaleTransformerInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Payment\Handler\PaymentDoneHandler;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Quote\Repository\QuoteRepositoryInterface;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentPriceResolverInterface;
use Symfony\Component\Form\Extension\Core\Type;
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
     * @var QuoteRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var ShipmentPriceResolverInterface
     */
    protected $shipmentPriceResolver;

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
     * @var PaymentDoneHandler
     */
    protected $paymentHandler;


    /**
     * Constructor.
     *
     * @param OrderRepositoryInterface       $orderRepository
     * @param QuoteRepositoryInterface       $quoteRepository
     * @param ShipmentPriceResolverInterface $shipmentPriceResolver
     * @param PaymentManager                 $paymentCheckout
     * @param PaymentHelper                  $paymentHelper
     * @param SaleTransformerInterface       $saleTransformer
     * @param PaymentDoneHandler             $paymentHandler
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        QuoteRepositoryInterface $quoteRepository,
        ShipmentPriceResolverInterface $shipmentPriceResolver,
        PaymentManager $paymentCheckout,
        PaymentHelper $paymentHelper,
        SaleTransformerInterface $saleTransformer,
        PaymentDoneHandler $paymentHandler
    ) {
        $this->orderRepository = $orderRepository;
        $this->quoteRepository = $quoteRepository;
        $this->shipmentPriceResolver = $shipmentPriceResolver;
        $this->paymentCheckout = $paymentCheckout;
        $this->paymentHelper = $paymentHelper;
        $this->saleTransformer = $saleTransformer;
        $this->paymentHandler = $paymentHandler;
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
                $this->getCartHelper()->getCartProvider()->updateCustomerGroupAndCurrency();

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
     * Quote transformation action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function quoteAction(Request $request)
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
            $this->updateShipmentAmount($cart);

            // TODO save cart ?

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

        return $this->render('EkynaCommerceBundle:Cart/Checkout:quote.html.twig', [
            'cart' => $cart,
            'form' => $form->createView(),
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
            $this->updateShipmentAmount($cart);

            $this->saveCart();

            return $this->redirect($this->generateUrl('ekyna_commerce_cart_checkout_payment'));
        }

        $view = $form->createView();

        return $this->render('EkynaCommerceBundle:Cart/Checkout:shipment.html.twig', [
            'cart' => $cart,
            'form' => $view,
        ]);
    }

    /**
     * Updates the sale shipment amount.
     *
     * @param SaleInterface $sale
     */
    private function updateShipmentAmount(SaleInterface $sale)
    {
        if (!$this->shipmentPriceResolver->hasFreeShipping($sale)) {
            $country = $sale->getDeliveryCountry();
            $method = $sale->getShipmentMethod();
            $weight = $sale->getWeightTotal();

            if ($country && $method) {
                $price = $this
                    ->shipmentPriceResolver
                    ->getPriceByCountryAndMethodAndWeight($country, $method, $weight);

                if (null !== $price) {
                    $sale->setShipmentAmount($price->getNetPrice());
                }

                return;
            }
        }

        $sale->setShipmentAmount(0);
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

            return $this->redirect($this->generateUrl('ekyna_commerce_cart_checkout_shipment'));
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
        if ($request->isXmlHttpRequest()) {
            throw new NotFoundHttpException("XHR is not supported.");
        }

        $payum = $this->paymentHandler->getPayum();

        $token = $payum->getHttpRequestVerifier()->verify($request);

        $gateway = $payum->getGateway($token->getGatewayName());

        $gateway->execute($done = new Status($token));

        $payum->getHttpRequestVerifier()->invalidate($token);

        /** @var PaymentInterface $payment */
        $payment = $done->getFirstModel();

        $sale = $this->paymentHandler->handle($payment);

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
        if ($currentCustomer && $currentCustomer->hasParent()) {
            $currentCustomer = $currentCustomer->getParent();
        }

        if ($orderCustomer !== $currentCustomer) {
            throw new AccessDeniedHttpException();
        }

        return $this->render('EkynaCommerceBundle:Cart/Checkout:confirmation.html.twig', [
            'order' => $order,
        ]);
    }
}
