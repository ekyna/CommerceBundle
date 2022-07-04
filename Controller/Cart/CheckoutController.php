<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Cart;

use Ekyna\Bundle\CommerceBundle\Event\CheckoutEvent;
use Ekyna\Bundle\CommerceBundle\Factory\QuoteFactory;
use Ekyna\Bundle\CommerceBundle\Form\Type\Checkout\ShipmentType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleTransformType;
use Ekyna\Bundle\CommerceBundle\Service\Payment\CheckoutManager;
use Ekyna\Bundle\CommerceBundle\Service\Payment\PaymentHelper;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Bundle\UiBundle\Service\FlashHelper;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\SaleStepValidatorInterface;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Common\Transformer\SaleTransformerInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Features;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function Symfony\Component\Translation\t;

/**
 * Class CheckoutController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Cart
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CheckoutController extends AbstractController
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly QuoteFactory $quoteFactory,
        private readonly CheckoutManager $checkoutManager,
        private readonly PaymentHelper $paymentHelper,
        private readonly SaleTransformerInterface $saleTransformer,
        private readonly FormFactoryInterface $formFactory,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly FlashHelper $flashHelper
    ) {
    }

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
                $view->addMessage($this->translate('checkout.message.shipment_defaults', [], 'EkynaCommerce'));
            }

            if ($cart->isLocked()) {
                $view->addAlert($this->translate('checkout.message.unlock', [
                    '{{url}}' => $this->generateUrl('ekyna_commerce_cart_checkout_unlock'),
                ], 'EkynaCommerce'));
            }

            $parameters['view'] = $view;
        }

        $parameters['controls'] = $this->buildCartControls($cart);

        if ($request->isXmlHttpRequest()) {
            return $this->render('@EkynaCommerce/Cart/response.xml.twig', $parameters);
        }

        if (null !== $cart) {
            $this->dispatcher->dispatch(new CheckoutEvent($cart), CheckoutEvent::EVENT_INIT);
        }

        return $this->render('@EkynaCommerce/Cart/Checkout/index.html.twig', $parameters);
    }

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
            ->formFactory
            ->create(SaleTransformType::class, $cart, [
                'action'  => $this->generateUrl('ekyna_commerce_cart_checkout_quote'),
                'method'  => 'POST',
                'message' => t('checkout.message.quote_confirm', [], 'EkynaCommerce'),
            ]);

        FormUtil::addFooter($form, [
            'submit_label' => t('button.transform', [], 'EkynaUi'),
            'submit_class' => 'success',
            'cancel_path'  => $cancelPath,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // New quote
            $quote = $this->quoteFactory->create(false);
            $quote->setEditable(true);

            // Initialize transformation
            $this->saleTransformer->initialize($cart, $quote);

            // Transform
            if (null === $event = $this->saleTransformer->transform()) {
                // Redirect to quote details
                return $this->redirect($this->generateUrl('ekyna_commerce_account_quote_read', [
                    'number' => $quote->getNumber(),
                ]));
            }

            $this->flashHelper->fromEvent($event);
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
            ->formFactory
            ->create(ShipmentType::class, $cart, [
                'action' => $this->generateUrl('ekyna_commerce_cart_checkout_shipment'),
                'method' => 'POST',
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->saveCart();

            return $this->redirect($this->generateUrl('ekyna_commerce_cart_checkout_payment'));
        }

        $this->dispatcher->dispatch(new CheckoutEvent($cart), CheckoutEvent::EVENT_SHIPMENT_STEP);

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

        $this->dispatcher->dispatch(new CheckoutEvent($cart), CheckoutEvent::EVENT_PAYMENT_STEP);

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

        foreach ($cart->getPayments(true) as $payment) {
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

    public function status(Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('XHR is not supported.');
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
            throw new UnexpectedTypeException($sale, CartInterface::class);
        }

        // Else go back to payments
        return $this->redirect($this->generateUrl('ekyna_commerce_cart_checkout_payment'));
    }

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
                $message = t(
                    'checkout.message.confirmation_access_denied',
                    ['{url}' => $this->generateUrl('ekyna_user_account_index')],
                    'EkynaCommerce'
                );

                $this->flashHelper->addFlash($message, 'warning');

                return new RedirectResponse(
                    $this->generateUrl('ekyna_commerce_cart_checkout_index')
                );
            }
        }

        $this->dispatcher->dispatch(new CheckoutEvent($order), CheckoutEvent::EVENT_CONFIRMATION);

        return $this->render('@EkynaCommerce/Cart/Checkout/confirmation.html.twig', [
            'order' => $order,
        ]);
    }
}
