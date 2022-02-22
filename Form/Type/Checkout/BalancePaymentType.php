<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Checkout;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentTermInterface;
use Ekyna\Component\Commerce\Payment\Updater\PaymentUpdaterInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;

use function Symfony\Component\Translation\t;

/**
 * Class BalancePaymentType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Checkout
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BalancePaymentType extends AbstractType
{
    private PaymentUpdaterInterface $paymentUpdater;

    public function __construct(PaymentUpdaterInterface $paymentUpdater)
    {
        $this->paymentUpdater = $paymentUpdater;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (0 < $options['available_amount']) {
            $builder
                ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options): void {
                    /** @var PaymentInterface $payment */
                    $payment = $event->getData();
                    $form = $event->getForm();

                    $fieldOptions = [
                        'label'    => t('field.amount', [], 'EkynaUi'),
                        'currency' => $payment->getCurrency()->getCode(),
                        'decimal'  => true,
                        'disabled' => !empty($options['lock_message']),
                    ];

                    if (!$payment->isRefund()) {
                        $fieldOptions['constraints'] = [
                            new LessThanOrEqual([
                                'value'  => min($options['available_amount'], $payment->getAmount()),
                                'groups' => ['Default', 'Checkout'],
                            ]),
                        ];
                    }

                    $form->add('amount', MoneyType::class, $fieldOptions);
                })
                ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options): void {
                    /** @var PaymentInterface $payment */
                    $payment = $event->getData();

                    $this->paymentUpdater->fixRealAmount($payment);
                }, -2048);
        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        /** @var PaymentInterface $payment */
        $payment = $form->getData();

        $view->vars['currency_code'] = $payment->getCurrency()->getCode();
        $view->vars['payment_term'] = $options['payment_term'];
        $view->vars['available_amount'] = $options['available_amount'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'available_amount' => new Decimal(0),
                'payment_term'     => null,
            ])
            ->setAllowedTypes('available_amount', ['int', Decimal::class])
            ->setAllowedTypes('payment_term', [PaymentTermInterface::class, 'null']);
    }

    public function getParent(): ?string
    {
        return PaymentType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_checkout_balance_payment';
    }
}
