<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Payment;

use Ekyna\Bundle\CommerceBundle\Event\CheckoutPaymentEvent;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Payment\Factory\PaymentFactoryInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Repository\PaymentMethodRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CheckoutManager
 *
 * This class generates and processes the payment forms for every payment methods available.
 *
 * @package Ekyna\Bundle\CommerceBundle\Service\Payment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CheckoutManager
{
    /**
     * @var PaymentMethodRepositoryInterface
     */
    private $methodRepository;

    /**
     * @var PaymentFactoryInterface
     */
    private $paymentFactory;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @var array|\Symfony\Component\Form\FormInterface[]
     */
    private $forms;


    /**
     * Constructor.
     *
     * @param PaymentMethodRepositoryInterface $methodRepository
     * @param PaymentFactoryInterface          $paymentFactory
     * @param EventDispatcherInterface         $eventDispatcher
     */
    public function __construct(
        PaymentMethodRepositoryInterface $methodRepository,
        PaymentFactoryInterface $paymentFactory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->methodRepository = $methodRepository;
        $this->paymentFactory = $paymentFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Initializes from the given sale by creating a form
     * for each available payment methods.
     *
     * @param SaleInterface $sale
     * @param string        $action
     * @param bool          $admin
     */
    public function initialize(SaleInterface $sale, $action, $admin = false): void
    {
        $this->forms = [];

        $currency = $sale->getCurrency();

        /** @var \Ekyna\Bundle\CommerceBundle\Model\PaymentMethodInterface[] $methods */
        $methods = $admin
            ? $this->methodRepository->findEnabled($currency)
            : $this->methodRepository->findAvailable($currency);

        if (empty($methods)) {
            throw new RuntimeException("No payment method available.");
        }

        foreach ($methods as $method) {
            $payment = $this->paymentFactory->createPayment($sale, $method);

            // Amount and currency have been set by the factory
            $payment->setMethod($method);

            $event = new CheckoutPaymentEvent($sale, $payment, [
                'action'     => $action,
                'admin_mode' => $admin,
            ]);

            $this->eventDispatcher->dispatch(CheckoutPaymentEvent::BUILD_FORM, $event);

            if (null !== $form = $event->getForm()) {
                if (isset($this->forms[$form->getName()])) {
                    throw new InvalidArgumentException("Form with name '{$form->getName()}' is already registered.");
                }

                $this->forms[$form->getName()] = $form;
            }
        }

        $this->initialized = true;
    }

    /**
     * Handles the request and returns the resulting payment
     * if one of the forms has been submitted and is valid.
     *
     * @param Request $request
     *
     * @return PaymentInterface|null
     */
    public function handleRequest(Request $request)
    {
        if (!$this->initialized) {
            throw new RuntimeException("The 'initialize' method must be called first.");
        }

        $this->initialized = false;

        foreach ($this->forms as $form) {
            $form->handleRequest($request);

            /** @noinspection PhpUndefinedMethodInspection */
            if ($form->isSubmitted() && $form->isValid() && $form->get('submit')->isClicked()) {
                return $form->getData();
            }
        }

        return null;
    }

    /**
     * Returns the payment forms views.
     *
     * @return array|\Symfony\Component\Form\FormView[]
     */
    public function getFormsViews()
    {
        $views = [];

        foreach ($this->forms as $form) {
            $views[] = $form->createView();
        }

        return $views;
    }
}
