<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Payment;

use Ekyna\Bundle\CommerceBundle\Event\CheckoutPaymentEvent;
use Ekyna\Component\Commerce\Bridge\Payum\CreditBalance\Constants as Credit;
use Ekyna\Component\Commerce\Bridge\Payum\OutstandingBalance\Constants as Outstanding;
use Ekyna\Component\Commerce\Bridge\Payum\Offline\Constants as Offline;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\LogicException;
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
        $this->paymentFactory   = $paymentFactory;
        $this->eventDispatcher  = $eventDispatcher;
    }

    /**
     * Initializes from the given sale by creating a form for each available payment methods.
     *
     * @param SaleInterface $sale
     * @param string        $action
     * @param bool          $refund
     * @param bool          $admin
     */
    public function initialize(SaleInterface $sale, string $action, bool $refund = false, bool $admin = false): void
    {
        if ($refund && !$admin) {
            throw new RuntimeException("Refund can't be initialized without admin mode.");
        }

        $this->forms = [];

        $methods = $this->getMethods($sale, $refund, $admin);
        if (empty($methods)) {
            throw new RuntimeException("No payment method available.");
        }

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
     * Returns the available payment methods for the given sale.
     *
     * @param SaleInterface $sale
     * @param bool          $refund
     * @param bool          $admin
     *
     * @return \Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface[]
     */
    protected function getMethods(SaleInterface $sale, bool $refund = false , bool $admin = false)
    {
        $customer = $sale->getCustomer();

        if ($refund) {
            if (!$admin) {
                throw new LogicException("Only administrators can create refunds.");
            }

            // TODO Methods that support payum refund action
            $methods = $this->methodRepository->findByFactoryName(Offline::FACTORY_NAME, !$admin);

            if ($sale->getPaymentTerm()) {
                foreach ($this->methodRepository->findByFactoryName(Outstanding::FACTORY_NAME, !$admin) as $method) {
                    $methods[] = $method;
                }
            }

            if ($customer) {
                foreach ($this->methodRepository->findByFactoryName(Credit::FACTORY_NAME, !$admin) as $method) {
                    $methods[] = $method;
                }
            }

            return $methods;
        }

        if ($customer && ($default = $customer->getDefaultPaymentMethod())) {
            return [$default];
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

            return $methods;
        }

        $currency = $sale->getCurrency();

        return $admin
            ? $this->methodRepository->findEnabled($currency)
            : $this->methodRepository->findAvailable($currency);
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
