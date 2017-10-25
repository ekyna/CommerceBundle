<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Payment;

use Ekyna\Bundle\CommerceBundle\Service\Payum\Request\Done;
use Ekyna\Component\Commerce\Payment\Event\PaymentEvent;
use Ekyna\Component\Commerce\Payment\Event\PaymentEvents;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Payum\Core\Payum;
use Payum\Core\Request\Notify;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class AbstractController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Payment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractController
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
        $this->debug = $debug;
    }

    /**
     * Cancel payment action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function cancelAction(Request $request)
    {
        $payment = $this->findPaymentByRequest($request);

        /*if (!PaymentStates::isPaidState($payment->getState())) {
            throw new NotFoundHttpException("This payment can't be cancelled.");
        }*/

        $event = $this->dispatcher->dispatch(
            PaymentEvents::CANCEL,
            new PaymentEvent($payment)
        );
        if ($event->hasResponse()) {
            return $event->getResponse();
        }

        /** @var \Ekyna\Bundle\CommerceBundle\Model\PaymentMethodInterface $method */
        $method = $payment->getMethod();

        $options = $this->getDoneOptions();

        /** @var \Payum\Core\Security\TokenInterface $cancelToken */
        /** @noinspection PhpUndefinedMethodInspection */
        $cancelToken = $this->getTokenFactory()->createCancelToken(
            $method->getGatewayName(),
            $payment,
            isset($options['route']) ? $options['route'] : null,
            isset($options['parameters']) ? $options['parameters'] : []
        );

        return $this->redirect($cancelToken->getTargetUrl());
    }

    /**
     * Capture payment action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function captureAction(Request $request)
    {
        $payment = $this->findPaymentByRequest($request);

        if ($payment->getState() !== PaymentStates::STATE_NEW) {
            throw new NotFoundHttpException('This payment has already been captured.');
        }

        $event = $this->dispatcher->dispatch(
            PaymentEvents::CAPTURE,
            new PaymentEvent($payment)
        );
        if ($event->hasResponse()) {
            return $event->getResponse();
        }

        /** @var \Ekyna\Bundle\CommerceBundle\Model\PaymentMethodInterface $method */
        $method = $payment->getMethod();

        $options = $this->getDoneOptions();

        $captureToken = $this->getTokenFactory()->createCaptureToken(
            $method->getGatewayName(),
            $payment,
            isset($options['route']) ? $options['route'] : null,
            isset($options['parameters']) ? $options['parameters'] : []
        );

        return $this->redirect($captureToken->getTargetUrl());
    }

    /**
     * Notify payment action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function notifyAction(Request $request)
    {
        $token = $this->getHttpRequestVerifier()->verify($request);

        $gateway = $this->payum->getGateway($token->getGatewayName());

        $gateway->execute($notify = new Notify($token));

        $event = $this->dispatcher->dispatch(
            PaymentEvents::DONE,
            new PaymentEvent($notify->getFirstModel())
        );
        if ($event->hasResponse()) {
            return $event->getResponse();
        }

        return new Response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Done payment action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function doneAction(Request $request)
    {
        $token = $this->getHttpRequestVerifier()->verify($request);

        $gateway = $this->payum->getGateway($token->getGatewayName());

        $gateway->execute($done = new Done($token));

        if (!$this->debug) {
            $this->getHttpRequestVerifier()->invalidate($token);
        }

        /** @var PaymentInterface $payment */
        $payment = $done->getFirstModel();

        $event = $this->dispatcher->dispatch(
            PaymentEvents::DONE,
            new PaymentEvent($done->getFirstModel())
        );
        if ($event->hasResponse()) {
            return $event->getResponse();
        }

        return $this->afterDone($request, $payment);
    }

    /**
     * @param Request $request
     *
     * @return \Ekyna\Component\Commerce\Payment\Model\PaymentInterface
     */
    abstract protected function findPaymentByRequest(Request $request);

    /**
     * Returns the capture options.
     *
     * @return array
     */
    abstract protected function getDoneOptions();

    /**
     * Returns the after done url.
     *
     * @param Request          $request
     * @param PaymentInterface $payment
     *
     * @return Response
     */
    abstract protected function afterDone(Request $request, PaymentInterface $payment);

    /**
     * Returns the payum.
     *
     * @return Payum
     */
    protected function getPayum()
    {
        return $this->payum;
    }

    /**
     * Returns the payum token verifier.
     *
     * @return \Payum\Core\Security\HttpRequestVerifierInterface
     */
    protected function getHttpRequestVerifier()
    {
        return $this->payum->getHttpRequestVerifier();
    }

    /**
     * Returns the payum token factory.
     *
     * @return \Payum\Core\Security\GenericTokenFactoryInterface
     */
    protected function getTokenFactory()
    {
        return $this->payum->getTokenFactory();
    }

    /**
     * Generates the url.
     *
     * @param string $name
     * @param array  $parameters
     * @param int    $referenceType
     *
     * @return string
     *
     * @see UrlGeneratorInterface::generate()
     */
    protected function generateUrl($name, $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->urlGenerator->generate($name, $parameters, $referenceType);
    }

    /**
     * Redirect to the given url.
     *
     * @param string $url
     *
     * @return RedirectResponse
     */
    protected function redirect($url)
    {
        return new RedirectResponse($url);
    }
}
