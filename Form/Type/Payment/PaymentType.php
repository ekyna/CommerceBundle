<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Payment;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\MoneyType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CurrencyChoiceType;
use Ekyna\Bundle\CommerceBundle\Model\PaymentStates as BStates;
use Ekyna\Bundle\ResourceBundle\Form\Type\ConstantChoiceType;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Symfony\Component\Form;
use Symfony\Component\Form\Extension\Core\Type;

/**
 * Class PaymentType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Payment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentType extends ResourceFormType
{
    /**
     * @inheritDoc
     */
    public function buildForm(Form\FormBuilderInterface $builder, array $options)
    {
        if (!$options['admin_mode']) {
            throw new LogicException("This form should not be used a public pages.");
        }

        $builder->addEventListener(Form\FormEvents::PRE_SET_DATA, function (Form\FormEvent $event) use ($options) {
            $form = $event->getForm();

            $payment = $event->getData();
            if (!$payment instanceof PaymentInterface) {
                throw new LogicException("Expected instance of " . PaymentInterface::class);
            }
            if (null === $payment->getId()) {
                throw new LogicException("This form should be only used to edit payment.");
            }
            if (null === $currency = $payment->getCurrency()) {
                throw new LogicException("Payment currency must be set.");
            }
            if (null === $method = $payment->getMethod()) {
                throw new LogicException("Payment method must be set.");
            }

            $methodDisabled = !$method->isManual();
            $amountDisabled = !($method->isManual() || $method->isOutstanding() || $method->isCredit());

            $form
                ->add('amount', MoneyType::class, [
                    'label'    => 'ekyna_core.field.amount',
                    'currency' => $currency->getCode(),
                    'disabled' => $amountDisabled,
                ])
                ->add('number', Type\TextType::class, [
                    'label'    => 'ekyna_core.field.number',
                    'disabled' => true,
                ])
                ->add('currency', CurrencyChoiceType::class, [
                    'disabled' => true,
                ])
                ->add('state', ConstantChoiceType::class, [
                    'label'    => 'ekyna_core.field.status',
                    'class'    => BStates::class,
                    'disabled' => true,
                ])
                ->add('method', PaymentMethodChoiceType::class, [
                    'disabled'    => $methodDisabled,
                    'enabled'     => true,            // Exclude disabled methods
                    'public'      => false,           // Include private methods
                    'offline'     => true,            // Include offline factories
                    'credit'      => $methodDisabled, // If disabled, include credit factory
                    'outstanding' => $methodDisabled, // If disabled, include outstanding factory
                ])
                ->add('completedAt', Type\DateTimeType::class, [
                    'label'    => 'ekyna_core.field.completed_at',
                    'required' => PaymentStates::isPaidState($payment->getState()),
                    'disabled' => $methodDisabled,
                ])
                ->add('description', Type\TextareaType::class, [
                    'label'    => 'ekyna_commerce.field.description',
                    'required' => false,
                ]);
        });
    }
}
