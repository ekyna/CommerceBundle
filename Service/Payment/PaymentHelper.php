<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Payment;

use Ekyna\Component\Commerce\Bridge\Payum\Request\Status;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Payment\Event\PaymentEvent;
use Ekyna\Component\Commerce\Payment\Event\PaymentEvents;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Payum\Core\Payum;
use Payum\Core\Request\Notify;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PaymentHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Payment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentHelper
{
    /**
     * @var Payum
     */
    private $payum;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var boolean
     */
    private $debug;


    /**
     * Constructor.
     *
     * @param Payum                    $payum
     * @param EventDispatcherInterface $dispatcher
     * @param bool                     $debug
     */
    public function __construct(
        Payum $payum,
        EventDispatcherInterface $dispatcher,
        $debug = false
    ) {
        $this->payum = $payum;
        $this->dispatcher = $dispatcher;
        $this->debug = (bool)$debug;
    }

    /**
     * Captures the payment.
     *
     * @param PaymentInterface $payment
     * @param string           $afterUrl
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function capture(PaymentInterface $payment, $afterUrl)
    {
        $this->validateUrl($afterUrl);

        if (null !== $response = $this->dispatch($payment, PaymentEvents::CAPTURE, $afterUrl)) {
            return $response;
        }

        $token = $this->createToken($payment, 'ekyna_commerce_payment_capture', $afterUrl);

        return new RedirectResponse($token->getTargetUrl());
    }

    /**
     * Cancels the payment.
     *
     * @param PaymentInterface $payment
     * @param string           $afterUrl
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cancel(PaymentInterface $payment, $afterUrl)
    {
        $this->validateUrl($afterUrl);

        if (null !== $response = $this->dispatch($payment, PaymentEvents::CANCEL, $afterUrl)) {
            return $response;
        }

        $token = $this->createToken($payment, 'ekyna_commerce_payment_cancel', $afterUrl);

        return new RedirectResponse($token->getTargetUrl());
    }

    /**
     * Hangs the payment.
     *
     * @param PaymentInterface $payment
     * @param string           $afterUrl
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function hang(PaymentInterface $payment, $afterUrl)
    {
        $this->validateUrl($afterUrl);

        if (null !== $response = $this->dispatch($payment, PaymentEvents::HANG, $afterUrl)) {
            return $response;
        }

        $token = $this->createToken($payment, 'ekyna_commerce_payment_hang', $afterUrl);

        return new RedirectResponse($token->getTargetUrl());
    }

    /**
     * Accepts the payment.
     *
     * @param PaymentInterface $payment
     * @param string           $afterUrl
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function accept(PaymentInterface $payment, $afterUrl)
    {
        $this->validateUrl($afterUrl);

        if (null !== $response = $this->dispatch($payment, PaymentEvents::ACCEPT, $afterUrl)) {
            return $response;
        }

        $token = $this->createToken($payment, 'ekyna_commerce_payment_accept', $afterUrl);

        return new RedirectResponse($token->getTargetUrl());
    }

    /**
     * Refunds the payment.
     *
     * @param PaymentInterface $payment
     * @param string           $afterUrl
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function refund(PaymentInterface $payment, $afterUrl)
    {
        $this->validateUrl($afterUrl);

        if (null !== $response = $this->dispatch($payment, PaymentEvents::REFUND, $afterUrl)) {
            return $response;
        }

        $token = $this->createToken($payment, 'ekyna_commerce_payment_refund', $afterUrl);

        return new RedirectResponse($token->getTargetUrl());
    }

    /**
     * Handle payment notify.
     *
     * @param Request $request
     *
     * @return PaymentInterface|null
     */
    public function notify(Request $request)
    {
        $token = $this->payum->getHttpRequestVerifier()->verify($request);

        $gateway = $this->payum->getGateway($token->getGatewayName());

        $gateway->execute($notify = new Notify($token));

        if (!$this->debug) {
            $this->payum->getHttpRequestVerifier()->invalidate($token);
        }

        /** @var PaymentInterface $payment */
        $payment = $notify->getFirstModel();

        $event = $this->dispatcher->dispatch(PaymentEvents::STATUS, new PaymentEvent($payment));

        return $event->getPayment();
    }

    /**
     * Updates the payment status.
     *
     * @param Request $request
     *
     * @return PaymentInterface|null
     */
    public function status(Request $request)
    {
        $token = $this->payum->getHttpRequestVerifier()->verify($request);

        $gateway = $this->payum->getGateway($token->getGatewayName());

        $gateway->execute($done = new Status($token));

        if (!$this->debug) {
            $this->payum->getHttpRequestVerifier()->invalidate($token);
        }

        /** @var PaymentInterface $payment */
        $payment = $done->getFirstModel();

        $event = $this->dispatcher->dispatch(PaymentEvents::STATUS, new PaymentEvent($payment));

        return $event->getPayment();
    }

    /**
     * Creates a payment token.
     *
     * @param PaymentInterface $payment
     * @param string           $url
     * @param string           $afterUrl
     *
     * @return \Payum\Core\Security\TokenInterface
     */
    protected function createToken(PaymentInterface $payment, $url, $afterUrl)
    {
        /** @var \Ekyna\Bundle\CommerceBundle\Model\PaymentMethodInterface $method */
        $method = $payment->getMethod();

        $tokenFactory = $this->payum->getTokenFactory();

        $afterUrl = $tokenFactory
            ->createToken($method->getGatewayName(), $payment, $afterUrl)
            ->getTargetUrl();

        return $tokenFactory->createToken(
            $method->getGatewayName(),
            $payment,
            $url,
            [],
            $afterUrl
        );
    }

    /**
     * Dispatches the payment event.
     *
     * @param PaymentInterface $payment
     * @param string           $event
     * @param string           $redirect
     *
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    protected function dispatch(PaymentInterface $payment, $event, $redirect)
    {
        $event = $this->dispatcher->dispatch($event, new PaymentEvent($payment));

        if ($event->hasResponse()) {
            return $event->getResponse();
        } elseif ($event->isPropagationStopped()) {
            return new RedirectResponse($redirect);
        }

        return null;
    }

    /**
     * Validates the given absolute url.
     *
     * @param string $url
     */
    protected function validateUrl($url)
    {
        if (0 !== strpos($url, 'http')) {
            throw new InvalidArgumentException("Expected absolute url, got '$url'.");
        }
    }
}
