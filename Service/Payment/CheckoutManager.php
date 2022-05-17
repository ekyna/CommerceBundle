<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Payment;

use Ekyna\Bundle\CommerceBundle\Event\CheckoutPaymentEvent;
use Ekyna\Component\Commerce\Bridge\Payum\CreditBalance\Constants as Credit;
use Ekyna\Component\Commerce\Bridge\Payum\Offline\Constants as Offline;
use Ekyna\Component\Commerce\Bridge\Payum\OutstandingBalance\Constants as Outstanding;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Payment\Factory\PaymentFactoryInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface;
use Ekyna\Component\Commerce\Payment\Repository\PaymentMethodRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\SubmitButton;
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
    private PaymentMethodRepositoryInterface $methodRepository;
    private PaymentFactoryInterface          $paymentFactory;
    private EventDispatcherInterface         $eventDispatcher;

    private bool $initialized = false;
    /** @var array<FormInterface> */
    private array $forms;

    public function __construct(
        PaymentMethodRepositoryInterface $methodRepository,
        PaymentFactoryInterface          $paymentFactory,
        EventDispatcherInterface         $eventDispatcher
    ) {
        $this->methodRepository = $methodRepository;
        $this->paymentFactory = $paymentFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Initializes from the given sale by creating a form for each available payment methods.
     */
    public function initialize(SaleInterface $sale, string $action, bool $refund = false, bool $admin = false): void
    {
        if ($refund && !$admin) {
            throw new RuntimeException("Refund can't be initialized without admin mode.");
        }

        $this->forms = [];

        $methods = $this->getMethods($sale, $refund, $admin);

        foreach ($methods as $method) {
            if ($refund) {
                $payment = $this->paymentFactory->createRefund($sale, $method);
            } else {
                $payment = $this->paymentFactory->createPayment($sale, $method);
            }

            // Amount and currency have been set by the factory
            $payment->setMethod($method);

            $event = new CheckoutPaymentEvent($sale, $payment, [
                'action'     => $action,
                'admin_mode' => $admin,
            ]);

            $this->eventDispatcher->dispatch($event, CheckoutPaymentEvent::BUILD_FORM);

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
     * Returns the available payment methods for the given sale.
     *
     * @return array<PaymentMethodInterface>
     */
    protected function getMethods(SaleInterface $sale, bool $refund = false, bool $admin = false): array
    {
        $customer = $sale->getCustomer();

        if ($refund) {
            if (!$admin) {
                throw new LogicException('Only administrators can create refunds.');
            }

            // TODO Methods that support payum refund action
            $methods = $this->methodRepository->findByFactoryName(Offline::FACTORY_NAME, false);

            if ($sale->getPaymentTerm()) {
                foreach ($this->methodRepository->findByFactoryName(Outstanding::FACTORY_NAME, false) as $method) {
                    $methods[] = $method;
                }
            }

            if ($customer) {
                foreach ($this->methodRepository->findByFactoryName(Credit::FACTORY_NAME, false) as $method) {
                    $methods[] = $method;
                }
            }

            return $this->filterMethods($methods, $sale);
        }

        if ($customer && ($default = $customer->getDefaultPaymentMethod())) {
            return $this->filterMethods([$default], $sale);
        }

        if ($default = $sale->getPaymentMethod()) {
            $methods = [$default];

            if ($sale->getPaymentTerm()) {
                foreach ($this->methodRepository->findByFactoryName(Outstanding::FACTORY_NAME, !$admin) as $method) {
                    $methods[] = $method;
                }
            }

            if ($customer && (0 < $customer->getCreditBalance())) {
                foreach ($this->methodRepository->findByFactoryName(Credit::FACTORY_NAME, !$admin) as $method) {
                    $methods[] = $method;
                }
            }

            return $this->filterMethods($methods, $sale);
        }

        $currency = $sale->getCurrency();

        $methods = $admin
            ? $this->methodRepository->findEnabled($currency)
            : $this->methodRepository->findAvailable($currency);

        return $this->filterMethods($methods, $sale);
    }

    /**
     * @param array<PaymentMethodInterface> $methods
     *
     * @return array<PaymentMethodInterface>
     */
    protected function filterMethods(array $methods, SaleInterface $sale): array
    {
        $filtered = [];

        foreach ($methods as $method) {
            if ($method->isFactor() && (null === $sale->getPaymentTerm())) {
                continue;
            }

            $filtered[] = $method;
        }

        return $filtered;
    }

    /**
     * Handles the request and returns the resulting payment
     * if one of the forms has been submitted and is valid.
     */
    public function handleRequest(Request $request): ?PaymentInterface
    {
        if (!$this->initialized) {
            throw new RuntimeException('The \'initialize\' method must be called first.');
        }

        $this->initialized = false;

        foreach ($this->forms as $form) {
            $form->handleRequest($request);

            if (!($form->isSubmitted() && $form->isValid())) {
                continue;
            }

            $button = $form->get('submit');

            if (!$button instanceof SubmitButton) {
                throw new RuntimeException('Failed to retrieve payment form\'s submit button.');
            }

            if ($button->isClicked()) {
                return $form->getData();
            }
        }

        return null;
    }

    /**
     * Returns the payment forms views.
     *
     * @return array<FormView>
     */
    public function getFormsViews(): array
    {
        $views = [];

        foreach ($this->forms as $form) {
            $views[] = $form->createView();
        }

        return $views;
    }
}
