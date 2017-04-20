<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Event;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class CheckoutPaymentEvent
 * @package Ekyna\Bundle\CommerceBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CheckoutPaymentEvent extends Event
{
    public const BUILD_FORM = 'ekyna_commerce.checkout.build_payment_form';

    private SaleInterface    $sale;
    private PaymentInterface $payment;
    private array            $formOptions;
    private ?FormInterface   $form = null;


    public function __construct(SaleInterface $sale, PaymentInterface $payment, array $formOptions = [])
    {
        $this->sale = $sale;
        $this->payment = $payment;
        $this->formOptions = array_replace($formOptions, [
            'validation_groups' => ['Checkout'],
        ]);
    }

    public function getSale(): SaleInterface
    {
        return $this->sale;
    }

    public function getPayment(): PaymentInterface
    {
        return $this->payment;
    }

    public function getFormOptions(): array
    {
        return $this->formOptions;
    }

    public function setForm(?FormInterface $form): void
    {
        $this->form = $form;
    }

    public function getForm(): ?FormInterface
    {
        return $this->form;
    }
}
