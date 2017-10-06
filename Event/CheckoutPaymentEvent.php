<?php

namespace Ekyna\Bundle\CommerceBundle\Event;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\FormInterface;

/**
 * Class CheckoutPaymentEvent
 * @package Ekyna\Bundle\CommerceBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CheckoutPaymentEvent extends Event
{
    const BUILD_FORM = 'ekyna_commerce.checkout.build_payment_form';

    /**
     * @var SaleInterface
     */
    private $sale;

    /**
     * @var PaymentInterface
     */
    private $payment;

    /**
     * @var array
     */
    private $formOptions;

    /**
     * @var FormInterface
     */
    private $form;


    /**
     * Constructor.
     *
     * @param SaleInterface    $sale
     * @param PaymentInterface $payment
     * @param array            $formOptions
     */
    public function __construct(SaleInterface $sale, PaymentInterface $payment, array $formOptions)
    {
        $this->sale = $sale;
        $this->payment = $payment;
        $this->formOptions = array_replace($formOptions, [
            'validation_groups' => ['Checkout'],
        ]);
    }

    /**
     * Returns the sale.
     *
     * @return SaleInterface
     */
    public function getSale()
    {
        return $this->sale;
    }

    /**
     * Returns the payment.
     *
     * @return PaymentInterface
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * Returns the form options.
     *
     * @return array
     */
    public function getFormOptions()
    {
        return $this->formOptions;
    }

    /**
     * Returns the form.
     *
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Sets the form.
     *
     * @param FormInterface $form
     */
    public function setForm(FormInterface $form)
    {
        $this->form = $form;
    }
}
