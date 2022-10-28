<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Payment;

use Ekyna\Bundle\CommerceBundle\Model\PaymentMethodInterface;
use Ekyna\Component\Commerce\Bridge\Payum\Request\Status;
use Ekyna\Component\Commerce\Common\Locking\LockChecker;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Payment\Event\PaymentEvent;
use Ekyna\Component\Commerce\Payment\Event\PaymentEvents;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentTransitions;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Payum\Core\Request\Notify;
use Payum\Core\Security\TokenInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException as CacheException;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PaymentHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Payment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentHelper
{
    private Payum                    $payum;
    private LockChecker              $lockChecker;
    private EventDispatcherInterface $dispatcher;
    private CacheItemPoolInterface   $cache;
    private bool                     $debug;

    public function __construct(
        Payum                    $payum,
        LockChecker              $lockChecker,
        EventDispatcherInterface $dispatcher,
        CacheItemPoolInterface   $cache,
        bool                     $debug = false
    ) {
        $this->payum = $payum;
        $this->lockChecker = $lockChecker;
        $this->dispatcher = $dispatcher;
        $this->cache = $cache;
        $this->debug = $debug;
    }

    /**
     * Returns whether the payment can be canceled by the user.
     */
    public function isUserCancellable(PaymentInterface $payment): bool
    {
        return in_array(
            PaymentTransitions::TRANSITION_CANCEL,
            $this->getTransitions($payment, false),
            true
        );
    }

    /**
     * Returns the available transitions for the given payment.
     *
     * @return array<string>
     */
    public function getTransitions(PaymentInterface $payment, bool $admin): array
    {
        /** @var PaymentMethodInterface $method */
        $method = $payment->getMethod();

        $locked = $this->lockChecker->isLocked($payment);

        $key = sprintf(
            'gateway_%s_%s_%s_%s_transitions',
            $method->getGatewayName(),
            $payment->getState(),
            $admin ? 'admin' : 'customer',
            $locked ? 'locked' : 'opened'
        );

        if (!$this->debug && $this->cache->hasItem($key)) {
            try {
                return $this->cache->getItem($key)->get();
            } catch (CacheException) {
            }
        }

        $transitions = $this->resolveTransitions($payment, $admin);

        if (!$this->debug) {
            try {
                $item = $this->cache->getItem($key);
                $item->set($transitions);
                $this->cache->save($item);
            } catch (CacheException $e) {
            }
        }

        return $transitions;
    }

    /**
     * Captures the payment.
     */
    public function capture(PaymentInterface $payment, string $afterUrl): Response
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
     */
    public function cancel(PaymentInterface $payment, string $afterUrl): Response
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
     */
    public function hang(PaymentInterface $payment, string $afterUrl): Response
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
     */
    public function accept(PaymentInterface $payment, string $afterUrl): Response
    {
        $this->validateUrl($afterUrl);

        if (null !== $response = $this->dispatch($payment, PaymentEvents::ACCEPT, $afterUrl)) {
            return $response;
        }

        $token = $this->createToken($payment, 'ekyna_commerce_payment_accept', $afterUrl);

        return new RedirectResponse($token->getTargetUrl());
    }

    /**
     * Accepts the payment.
     */
    public function authorize(PaymentInterface $payment, string $afterUrl): Response
    {
        $this->validateUrl($afterUrl);

        if (null !== $response = $this->dispatch($payment, PaymentEvents::AUTHORIZE, $afterUrl)) {
            return $response;
        }

        $token = $this->createToken($payment, 'ekyna_commerce_payment_authorize', $afterUrl);

        return new RedirectResponse($token->getTargetUrl());
    }

    /**
     * Marks the payment as "payed-out".
     */
    public function payout(PaymentInterface $payment, string $afterUrl): Response
    {
        $this->validateUrl($afterUrl);

        if (null !== $response = $this->dispatch($payment, PaymentEvents::PAYOUT, $afterUrl)) {
            return $response;
        }

        $token = $this->createToken($payment, 'ekyna_commerce_payment_payout', $afterUrl);

        return new RedirectResponse($token->getTargetUrl());
    }

    /**
     * Refunds the payment.
     */
    public function refund(PaymentInterface $payment, string $afterUrl): Response
    {
        $this->validateUrl($afterUrl);

        if (null !== $response = $this->dispatch($payment, PaymentEvents::REFUND, $afterUrl)) {
            return $response;
        }

        $token = $this->createToken($payment, 'ekyna_commerce_payment_refund', $afterUrl);

        return new RedirectResponse($token->getTargetUrl());
    }

    /**
     * Refunds the payment.
     */
    public function reject(PaymentInterface $payment, string $afterUrl): Response
    {
        $this->validateUrl($afterUrl);

        if (null !== $response = $this->dispatch($payment, PaymentEvents::REJECT, $afterUrl)) {
            return $response;
        }

        $token = $this->createToken($payment, 'ekyna_commerce_payment_reject', $afterUrl);

        return new RedirectResponse($token->getTargetUrl());
    }

    /**
     * Handle payment notify.
     */
    public function notify(Request $request): ?PaymentInterface
    {
        $token = $this->payum->getHttpRequestVerifier()->verify($request);

        $gateway = $this->payum->getGateway($token->getGatewayName());

        $gateway->execute($notify = new Notify($token));

        if (!$this->debug) {
            $this->payum->getHttpRequestVerifier()->invalidate($token);
        }

        /** @var PaymentInterface $payment */
        $payment = $notify->getFirstModel();

        $event = $this->dispatcher->dispatch(new PaymentEvent($payment), PaymentEvents::STATUS);

        return $event->getPayment();
    }

    /**
     * Updates the payment status.
     */
    public function status(Request $request): ?PaymentInterface
    {
        $token = $this->payum->getHttpRequestVerifier()->verify($request);

        $gateway = $this->payum->getGateway($token->getGatewayName());

        $gateway->execute($done = new Status($token));

        if (!$this->debug) {
            $this->payum->getHttpRequestVerifier()->invalidate($token);
        }

        /** @var PaymentInterface $payment */
        $payment = $done->getFirstModel();

        $event = $this->dispatcher->dispatch(new PaymentEvent($payment), PaymentEvents::STATUS);

        return $event->getPayment();
    }

    /**
     * Resolves the available transitions for the given payment.
     *
     * @return array<string>
     * @throws ReflectionException
     */
    protected function resolveTransitions(PaymentInterface $payment, bool $admin = false): array
    {
        /** @var PaymentMethodInterface $method */
        $method = $payment->getMethod();
        $locked = $this->lockChecker->isLocked($payment);

        $gateway = $this->payum->getGateway($method->getGatewayName());
        $actions = $this->getGatewayActions($gateway);

        $available = PaymentTransitions::getAvailableTransitions($payment, $admin, $locked);
        $transitions = [];

        foreach ($available as $transition) {
            $class = PaymentTransitions::getRequestClass($transition);

            // Check with payment object
            $request = new $class($payment);
            foreach ($actions as $action) {
                if ($action instanceof ExecuteSameRequestWithModelDetailsAction) {
                    continue;
                }

                if ($action->supports($request)) {
                    $transitions[] = $transition;
                    continue 2;
                }
            }

            // Check with payment details (array object)
            $request = new $class($payment->getDetails());
            foreach ($actions as $action) {
                if ($action instanceof ExecuteSameRequestWithModelDetailsAction) {
                    continue;
                }

                if ($action->supports($request)) {
                    $transitions[] = $transition;
                    continue 2;
                }
            }
        }

        return $transitions;
    }

    /**
     * Returns the gateway actions.
     *
     * @return array<ActionInterface>
     * @throws ReflectionException
     */
    protected function getGatewayActions(GatewayInterface $gateway): array
    {
        $rc = new ReflectionClass(get_class($gateway));
        $rp = $rc->getProperty('actions');
        $rp->setAccessible(true);

        return $rp->getValue($gateway);
    }

    /**
     * Creates a payment token.
     */
    protected function createToken(PaymentInterface $payment, string $url, string $afterUrl): TokenInterface
    {
        /** @var PaymentMethodInterface $method */
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
     */
    protected function dispatch(PaymentInterface $payment, string $eventName, string $redirect): ?Response
    {
        $event = $this->dispatcher->dispatch(new PaymentEvent($payment), $eventName);

        if ($event->hasResponse()) {
            return $event->getResponse();
        } elseif ($event->isPropagationStopped()) {
            return new RedirectResponse($redirect);
        }

        return null;
    }

    /**
     * Validates the given absolute url.
     */
    protected function validateUrl(string $url): void
    {
        if (0 !== strpos($url, 'http')) {
            throw new InvalidArgumentException("Expected absolute url, got '$url'.");
        }
    }
}
