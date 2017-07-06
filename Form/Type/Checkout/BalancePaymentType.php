<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Checkout;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\MoneyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;

/**
 * Class BalancePaymentType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Checkout
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BalancePaymentType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (0 < $options['available_amount']) {
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
                /** @var \Ekyna\Component\Commerce\Payment\Model\PaymentInterface $payment */
                $payment = $event->getData();
                $form = $event->getForm();

                $max = min($options['available_amount'], $payment->getAmount());

                $form->add('amount', MoneyType::class, [
                    'label'       => 'ekyna_core.field.amount',
                    'currency'    => $payment->getCurrency()->getCode(),
                    'constraints' => [
                        new LessThanOrEqual($max),
                    ],
                ]);
            });
        }
    }

    /**
     * @inheritDoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        /** @var \Ekyna\Component\Commerce\Payment\Model\PaymentInterface $payment */
        $payment = $form->getData();

        $view->vars['currency_code'] = $payment->getCurrency()->getCode();
        $view->vars['available_amount'] = $options['available_amount'];
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('available_amount')
            ->setAllowedTypes('available_amount', ['int', 'float']);
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return PaymentType::class;
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_checkout_balance_payment';
    }
}
