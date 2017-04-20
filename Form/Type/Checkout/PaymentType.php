<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Checkout;

use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Contracts\Translation\TranslatorInterface;

use function Symfony\Component\Translation\t;

/**
 * Class PaymentType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Checkout
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentType extends AbstractType
{
    private TranslatorInterface $translator;


    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            /** @var PaymentInterface $payment */
            $payment = $event->getData();
            $form = $event->getForm();

            $method = $payment->getMethod();

            if ($options['admin_mode']) {
                $fieldOptions = [
                    'label'    => t('field.amount', [], 'EkynaUi'),
                    'decimal'  => true,
                    'currency' => $payment->getCurrency()->getCode(),
                    'disabled' => !empty($options['lock_message']),
                ];

                if (!$payment->isRefund()) {
                    $fieldOptions['constraints'] = [
                        new LessThanOrEqual($payment->getRealAmount()),
                    ];
                }

                $form->add('amount', Type\MoneyType::class, $fieldOptions);

                if ($method->isManual()) {
                    $form
                        ->add('completedAt', Type\DateTimeType::class, [
                            'label'    => t('field.completed_at', [], 'EkynaUi'),
                            'required' => PaymentStates::isCompletedState($payment->getState()),
                        ])
                        ->add('description', Type\TextareaType::class, [
                            'label'    => t('field.description', [], 'EkynaCommerce'),
                            'required' => false,
                        ]);
                }
            }

            $submitLabel = $payment->isRefund() ? 'checkout.payment.refund_by' : 'checkout.payment.pay_by';

            $form->add('submit', Type\SubmitType::class, [
                'label'              => $this->translator->trans($submitLabel, [
                    '%method%' => $method->getTitle(),
                ], 'EkynaCommerce'),
                'translation_domain' => false,
                'disabled'           => !empty($options['lock_message']),
            ]);
        });
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $payment = $form->getData();
        if (null === $payment) {
            throw new RuntimeException('Payment data must be set.');
        }

        if ($options['admin_mode']) {
            $view->vars['extended'] = true;
            $view->vars['attr']['class'] = 'extended';
        } else {
            $view->vars['extended'] = false;
        }

        $view->vars['payment'] = $payment;
        $view->vars['lock_message'] = $options['lock_message'];
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        FormUtil::addClass($view, 'checkout-payment');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class'   => PaymentInterface::class,
                'lock_message' => null,
            ])
            ->setAllowedTypes('lock_message', ['null', 'string']);
    }

    public function getBlockPrefix(): ?string
    {
        return 'ekyna_commerce_checkout_payment';
    }
}
