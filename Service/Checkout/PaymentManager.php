<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Checkout;

use Ekyna\Bundle\CommerceBundle\Event\CheckoutPaymentEvent;
use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Repository\PaymentMethodRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PaymentManager
 *
 * This class generates and processes the payment forms for every payment methods available.
 *
 * @package Ekyna\Bundle\CommerceBundle\Service\Payment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentManager
{
    /**
     * @var PaymentMethodRepositoryInterface
     */
    private $methodRepository;

    /**
     * @var SaleFactoryInterface
     */
    private $saleFactory;

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
     * @param SaleFactoryInterface             $saleFactory
     * @param EventDispatcherInterface         $eventDispatcher
     */
    public function __construct(
        PaymentMethodRepositoryInterface $methodRepository,
        SaleFactoryInterface $saleFactory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->methodRepository = $methodRepository;
        $this->saleFactory = $saleFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Initializes from the given sale by creating a form
     * for each available payment methods.
     *
     * @param SaleInterface $sale
     */
    public function initialize(SaleInterface $sale)
    {
        $this->forms = [];

        $amount = $sale->getGrandTotal() - $sale->getPaidTotal();

        /** @var \Ekyna\Bundle\CommerceBundle\Model\PaymentMethodInterface[] $methods */
        $methods = $this->methodRepository->findAvailable();
        if (empty($methods)) {
            throw new RuntimeException("No payment method available.");
        }

        foreach ($methods as $method) {
            $payment = $this->saleFactory->createPaymentForSale($sale);
            $payment
                ->setMethod($method)
                ->setAmount($amount);

            $event = new CheckoutPaymentEvent($sale, $payment);

            $this->eventDispatcher->dispatch(CheckoutPaymentEvent::BUILD_FORM, $event);

            if (null !== $form = $event->getForm()) {
                $form->add('submit', Type\SubmitType::class, [
                    'label' => $method->getTitle(),
                ]);

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

            if ($form->isSubmitted() && $form->isValid()) {
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
