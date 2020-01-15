<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Checkout;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\MoneyType;
use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
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
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;

/**
 * Class PaymentType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Checkout
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;


    /**
     * Constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            /** @var PaymentInterface $payment */
            $payment = $event->getData();
            $form    = $event->getForm();

            $method = $payment->getMethod();

            if ($options['admin_mode']) {
                $fieldOptions = [
                    'label'       => 'ekyna_core.field.amount',
                    'currency'    => $payment->getCurrency()->getCode(),
                    'disabled'    => !empty($options['lock_message']),
                ];

                if (!$payment->isRefund()) {
                    $fieldOptions['constraints'] = [
                        new LessThanOrEqual($payment->getRealAmount()),
                    ];
                }

                $form->add('amount', MoneyType::class, $fieldOptions);

                if ($method->isManual()) {
                    $form
                        ->add('completedAt', Type\DateTimeType::class, [
                            'label'    => 'ekyna_core.field.completed_at',
                            'required' => PaymentStates::isPaidState($payment->getState()),
                        ])
                        ->add('description', Type\TextareaType::class, [
                            'label'    => 'ekyna_commerce.field.description',
                            'required' => false,
                        ]);
                }
            }

            $submitLabel = $payment->isRefund()
                ? 'ekyna_commerce.checkout.payment.refund_by'
                : 'ekyna_commerce.checkout.payment.pay_by';

            $form->add('submit', Type\SubmitType::class, [
                'label'              => $this->translator->trans($submitLabel, [
                    '%method%' => $method->getTitle(),
                ]),
                'translation_domain' => false,
                'disabled'           => !empty($options['lock_message']),
            ]);
        });
    }

    /**
     * @inheritDoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $payment = $form->getData();
        if (null === $payment) {
            throw new RuntimeException("Payment data must be set.");
        }

        if ($options['admin_mode']) {
            $view->vars['extended']      = true;
            $view->vars['attr']['class'] = 'extended';
        } else {
            $view->vars['extended'] = false;
        }

        $view->vars['payment']      = $payment;
        $view->vars['lock_message'] = $options['lock_message'];
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        FormUtil::addClass($view, 'checkout-payment');
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'   => PaymentInterface::class,
                'lock_message' => null,
            ])
            ->setAllowedTypes('lock_message', ['null', 'string']);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_checkout_payment';
    }
}
