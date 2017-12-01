<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Payment;

use Ekyna\Component\Commerce\Bridge\Payum\Request\Status;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Payment\Event\PaymentEvent;
use Ekyna\Component\Commerce\Payment\Event\PaymentEvents;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Payum\Core\Payum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var boolean
     */
    private $debug;


    /**
     * Constructor.
     *
     * @param Payum                    $payum
     * @param EventDispatcherInterface $dispatcher
     * @param UrlGeneratorInterface    $urlGenerator
     * @param bool                     $debug
     */
    public function __construct(
        Payum $payum,
        EventDispatcherInterface $dispatcher,
        UrlGeneratorInterface $urlGenerator,
        $debug = false
    ) {
        $this->payum = $payum;
        $this->dispatcher = $dispatcher;
        $this->urlGenerator = $urlGenerator;
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
     *
     * @deprecated Do not refund payment -> use credit invoice
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
     * Updates the payment status.
     *
     * @param Request $request
     *
     * @return PaymentInterface
     */
    public function status(Request $request)
    {
        $token = $this->payum->getHttpRequestVerifier()->verify($request);

        $gateway = $this->payum->getGateway($token->getGatewayName());

        if (!$this->debug) {
            $this->payum->getHttpRequestVerifier()->invalidate($token);
        }

        $gateway->execute($done = new Status($token));

        /** @var PaymentInterface $payment */
        $payment = $done->getFirstModel();

        return $payment;
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
     * Generates an url for the given route and parameters.
     *
     * @param string $name
     * @param array  $parameters
     *
     * @return string
     */
    protected function generateUrl($name, array $parameters = [])
    {
        return $this->urlGenerator->generate($name, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
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
