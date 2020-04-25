<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Event\CheckoutPaymentEvent;
use Ekyna\Bundle\CommerceBundle\Form\Type\Checkout\BalancePaymentType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Checkout\PaymentType;
use Ekyna\Bundle\CommerceBundle\Model\QuoteInterface;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Customer\Model\CustomerStates;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Updater\PaymentUpdaterInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class PaymentCheckoutEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentCheckoutEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var PaymentUpdaterInterface
     */
    private $paymentUpdater;

    /**
     * @var CurrencyConverterInterface
     */
    private $currencyConverter;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var string
     */
    private $defaultCurrency;


    /**
     * Constructor.
     *
     * @param FormFactoryInterface       $formFactory
     * @param PaymentUpdaterInterface    $paymentUpdater
     * @param CurrencyConverterInterface $currencyConverter
     * @param TranslatorInterface        $translator
     * @param UrlGeneratorInterface      $urlGenerator
     * @param string                     $defaultCurrency
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        PaymentUpdaterInterface $paymentUpdater,
        CurrencyConverterInterface $currencyConverter,
        TranslatorInterface $translator,
        UrlGeneratorInterface $urlGenerator,
        string $defaultCurrency
    ) {
        $this->formFactory = $formFactory;
        $this->paymentUpdater = $paymentUpdater;
        $this->currencyConverter = $currencyConverter;
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
        $this->defaultCurrency = $defaultCurrency;
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

        if ($method->isCredit()) {
            $this->buildCreditForm($event);
        } elseif ($method->isOutstanding()) {
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
        $sale = $event->getSale();

        // Disallow non offline methods for fraudster
        $customer = $sale->getCustomer();
        if ($customer && ($customer->getState() === CustomerStates::STATE_FRAUDSTER)) {
            /** @var \Ekyna\Bundle\CommerceBundle\Model\PaymentMethodInterface $method */
            if (null === $method = $payment->getMethod()) {
                throw new RuntimeException("Payment's method must be set at this point.");
            }
            if (!$method->isManual()) {
                $event->stopPropagation();

                return;
            }
        }

        $form = $this
            ->formFactory
            ->createNamed($this->getFormName($payment), PaymentType::class, $payment, $event->getFormOptions());

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
        // Abort if child customer
        if ($customer->hasParent()) {
            $event->stopPropagation();

            return;
        }

        // TODO Refactor with \Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints\PaymentValidator

        $payment = $event->getPayment();
        $options = $event->getFormOptions();

        // If not refund, check customer balance
        if ($payment->isRefund()) {
            $options['available_amount'] = INF;
        } else {
            // Abort if customer has no fund
            if (0 >= $customer->getCreditBalance()) {
                $event->stopPropagation();

                return;
            }
            // Abort if deposit is not paid
            // TODO No: customer can pay the deposit with its credit balance
            if (0 < $sale->getDepositTotal()) {
                if (-1 === Money::compare($sale->getPaidTotal(), $sale->getDepositTotal(), $this->defaultCurrency)) {
                    $event->stopPropagation();

                    return;
                }
            }

            // Customer available fund
            $available = $customer->getCreditBalance();

            // If customer available fund is lower than the payment amount
            if ($available < $payment->getRealAmount()) {
                // Limit to available fund
                $this->paymentUpdater->updateRealAmount($payment, $available);
            }

            $available = $this->currencyConverter->convertWithSubject($available, $payment);

            $options['available_amount'] = $available;
        }

        $form = $this
            ->formFactory
            ->createNamed($this->getFormName($payment), BalancePaymentType::class, $payment, $options);

        $event->setForm($form);
    }

    /**
     * Builds the customer outstanding balance method payment form.
     *
     * @param CheckoutPaymentEvent $event
     */
    protected function buildOutstandingForm(CheckoutPaymentEvent $event)
    {
        $payment = $event->getPayment();
        // Abort if refund
        if ($payment->isRefund()) {
            return;
        }

        $sale = $event->getSale();

        // Abort if no customer
        if (null === $customer = $sale->getCustomer()) {
            $event->stopPropagation();

            return;
        }
        // Abort if child customer
        if ($customer->hasParent()) {
            $event->stopPropagation();

            return;
        }
        // Abort if no payment term
        if (null === $term = $sale->getPaymentTerm()) {
            $event->stopPropagation();

            return;
        }
        // Abort if outstanding payment has already been used
        if (0 < $sale->getOutstandingAccepted() || 0 < $sale->getOutstandingExpired()) {
            $event->stopPropagation();

            return;
        }
        // Abort if deposit is not paid
        if (0 < $sale->getDepositTotal()) {
            if (-1 === Money::compare($sale->getPaidTotal(), $sale->getDepositTotal(), $this->defaultCurrency)) {
                $event->stopPropagation();

                return;
            }
        }

        // If sale has a outstanding limit
        if ((0 < $limit = $sale->getOutstandingLimit()) && $customer->isOutstandingOverflow()) {
            // Use sale's balance
            $balance = -$sale->getOutstandingAccepted() - $sale->getOutstandingExpired();
        } else {
            // Use customer's limit and balance
            $limit = $customer->getOutstandingLimit();
            $balance = $customer->getOutstandingBalance();
        }

        $available = $limit + $balance;

        // Abort if non available fund
        if (0 >= $available) {
            $event->stopPropagation();

            return;
        }

        // If customer available fund is lower than the payment amount
        if ($available < $payment->getRealAmount()) {
            // Limit to available fund
            $this->paymentUpdater->updateRealAmount($payment, $available);
        }

        $available = $this->currencyConverter->convertWithSubject($available, $payment);

        $options = $event->getFormOptions();
        $options['available_amount'] = $available;

        if (!$options['admin_mode'] && $sale instanceof QuoteInterface && !$sale->hasVoucher()) {
            $options['lock_message'] = $this->translator->trans('ekyna_commerce.checkout.payment.voucher_mandatory', [
                '%url%' => $this->urlGenerator->generate('ekyna_commerce_account_quote_voucher', [
                    'number' => $sale->getNumber(),
                ]),
            ]);
            $options['disabled'] = true;
        } else {
            $options['payment_term'] = $term;
        }

        $form = $this
            ->formFactory
            ->createNamed($this->getFormName($payment), BalancePaymentType::class, $payment, $options);

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
