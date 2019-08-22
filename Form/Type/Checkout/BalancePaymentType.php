<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Checkout;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\MoneyType;
use Ekyna\Component\Commerce\Payment\Model\PaymentTermInterface;
use Ekyna\Component\Commerce\Payment\Updater\PaymentUpdaterInterface;
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
     * @var PaymentUpdaterInterface
     */
    private $paymentUpdater;

    /**
     * Constructor.
     *
     * @param PaymentUpdaterInterface $paymentUpdater
     */
    public function __construct(PaymentUpdaterInterface $paymentUpdater)
    {
        $this->paymentUpdater = $paymentUpdater;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (0 < $options['available_amount']) {
            $builder
                ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
                    /** @var \Ekyna\Component\Commerce\Payment\Model\PaymentInterface $payment */
                    $payment = $event->getData();
                    $form = $event->getForm();

                    $max = min($options['available_amount'], $payment->getAmount());

                    $form->add('amount', MoneyType::class, [
                        'label'       => 'ekyna_core.field.amount',
                        'currency'    => $payment->getCurrency()->getCode(),
                        'disabled'    => !empty($options['lock_message']),
                        'constraints' => [
                            new LessThanOrEqual($max),
                        ],
                    ]);
                })
                ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options) {
                    /** @var \Ekyna\Component\Commerce\Payment\Model\PaymentInterface $payment */
                    $payment = $event->getData();

                    $this->paymentUpdater->fixRealAmount($payment);
                }, -2048);
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
        $view->vars['payment_term'] = $options['payment_term'];
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('available_amount')
            ->setDefault('payment_term', null)
            ->setAllowedTypes('available_amount', ['int', 'float'])
            ->setAllowedTypes('payment_term', [PaymentTermInterface::class, 'null']);
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
