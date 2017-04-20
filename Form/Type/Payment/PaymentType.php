<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Payment;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CurrencyChoiceType;
use Ekyna\Bundle\CommerceBundle\Model\PaymentStates as BStates;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\ConstantChoiceType;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Symfony\Component\Form;
use Symfony\Component\Form\Extension\Core\Type;

use function Symfony\Component\Translation\t;

/**
 * Class PaymentType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Payment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentType extends AbstractResourceType
{
    public function buildForm(Form\FormBuilderInterface $builder, array $options): void
    {
        if (!$options['admin_mode']) {
            throw new LogicException('This form should not be used a public pages.');
        }

        $builder->addEventListener(Form\FormEvents::PRE_SET_DATA, function (Form\FormEvent $event) use ($options) {
            $form = $event->getForm();

            $payment = $event->getData();
            if (!$payment instanceof PaymentInterface) {
                throw new LogicException('Expected instance of ' . PaymentInterface::class);
            }
            if (null === $payment->getId()) {
                throw new LogicException('This form should be only used to edit payment.');
            }
            if (null === $currency = $payment->getCurrency()) {
                throw new LogicException('Payment currency must be set.');
            }
            if (null === $method = $payment->getMethod()) {
                throw new LogicException('Payment method must be set.');
            }

            $locked = $this->isLocked($payment);

            $methodDisabled = $locked || !$method->isManual() || PaymentStates::isPaidState($payment, true);
            $amountDisabled = $locked || !($method->isManual() || $method->isOutstanding() || $method->isCredit());

            $form
                ->add('amount', Type\MoneyType::class, [
                    'label'    => t('field.amount', [], 'EkynaUi'),
                    'decimal'  => true,
                    'currency' => $currency->getCode(),
                    'disabled' => $amountDisabled,
                ])
                ->add('number', Type\TextType::class, [
                    'label'    => t('field.number', [], 'EkynaUi'),
                    'required' => false,
                    'disabled' => true,
                ])
                ->add('currency', CurrencyChoiceType::class, [
                    'required' => false,
                    'disabled' => true,
                ])
                ->add('state', ConstantChoiceType::class, [
                    'label'    => t('field.status', [], 'EkynaUi'),
                    'class'    => BStates::class,
                    'required' => false,
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
                    'label'    => t('field.completed_at', [], 'EkynaUi'),
                    'required' => PaymentStates::isCompletedState($payment->getState()),
                    'disabled' => $methodDisabled,
                ])
                ->add('description', Type\TextareaType::class, [
                    'label'    => t('field.description', [], 'EkynaCommerce'),
                    'required' => false,
                ]);
        });
    }

    /**
     * Returns whether the payment is locked.
     */
    protected function isLocked(PaymentInterface $payment): bool
    {
        return false;
    }
}
