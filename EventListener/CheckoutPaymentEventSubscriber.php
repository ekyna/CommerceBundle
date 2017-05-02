<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Event\CheckoutPaymentEvent;
use Ekyna\Bundle\CommerceBundle\Form\Type\Checkout\BalancePaymentType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Checkout\PaymentType;
use Ekyna\Component\Commerce\Bridge\Payum\CreditBalance\Constants as Credit;
use Ekyna\Component\Commerce\Bridge\Payum\OutstandingBalance\Constants as Outstanding;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Class CheckoutPaymentEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CheckoutPaymentEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;


    /**
     * Constructor.
     *
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     *
     * @param CheckoutPaymentEvent $event
     */
    public function onBuildPaymentForm(CheckoutPaymentEvent $event)
    {
        $payment = $event->getPayment();

        /** @var \Ekyna\Bundle\CommerceBundle\Model\PaymentMethodInterface $method */
        if (null === $method = $payment->getMethod()) {
            throw new RuntimeException("Payment's method must be set at this point.");
        }

        if ($method->getFactoryName() === Credit::FACTORY_NAME) {
            $this->buildCreditForm($event);
        } elseif ($method->getFactoryName() === Outstanding::FACTORY_NAME) {
            $this->buildOutstandingForm($event);
        } else {
            $this->buildDefaultForm($event);
        }
    }

    /**
     * Builds the default payment form.
     *
     * @param CheckoutPaymentEvent $event
     */
    protected function buildDefaultForm(CheckoutPaymentEvent $event)
    {
        $payment = $event->getPayment();

        $form =$this
            ->formFactory
            ->createNamed($this->getFormName($payment), PaymentType::class, $payment);

        $event->setForm($form);
    }

    /**
     * Builds the customer balance method payment form.
     *
     * @param CheckoutPaymentEvent $event
     */
    protected function buildCreditForm(CheckoutPaymentEvent $event)
    {
        $sale = $event->getSale();

        // Abort if no customer
        if (null === $customer = $sale->getCustomer()) {
            $event->stopPropagation();
            return;
        }
        // Abort if customer has no fund
        if (0 >= $customer->getCreditBalance()) {
            $event->stopPropagation();
            return;
        }

        $payment = $event->getPayment();
        $available = $customer->getCreditBalance();
        if ($available < $payment->getAmount()) {
            $payment->setAmount($available);
        }

        $form = $this
            ->formFactory
            ->createNamed($this->getFormName($payment), BalancePaymentType::class, $payment, [
                'available_amount' => (float)$available
            ]);

        $event->setForm($form);
    }

    /**
     * Builds the customer outstanding balance method payment form.
     *
     * @param CheckoutPaymentEvent $event
     */
    protected function buildOutstandingForm(CheckoutPaymentEvent $event)
    {
        $sale = $event->getSale();

        // Abort if no customer
        if (null === $customer = $sale->getCustomer()) {
            $event->stopPropagation();
            return;
        }
        if (null === $sale->getPaymentTerm()) {
            $event->stopPropagation();
            return;
        }

        $payment = $event->getPayment();

        // Customer available fund
        // TODO logical/expected ?
        $floor = max($customer->getOutstandingLimit(), $sale->getOutstandingLimit());
        $available = $floor + $customer->getOutstandingBalance();

        // If customer available fund is lower than the payment amount
        if ($available < $payment->getAmount()) {
            $payment->setAmount($available);
        }

        $form = $this
            ->formFactory
            ->createNamed($this->getFormName($payment), BalancePaymentType::class, $payment, [
                'available_amount' => (float)$available
            ]);

        $event->setForm($form);
    }

    /**
     * Returns the form name for the given payment.
     *
     * @param PaymentInterface $payment
     *
     * @return string
     */
    protected function getFormName(PaymentInterface $payment)
    {
        return 'method_' . $payment->getMethod()->getId();
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            CheckoutPaymentEvent::BUILD_FORM => ['onBuildPaymentForm', -1024],
        ];
    }
}
