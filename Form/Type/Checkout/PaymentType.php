<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Checkout;

use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

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
            $form = $event->getForm();

            if (0 < $payment->getAmount()) {
                $form->add('submit', SubmitType::class, [
                    'label'              => $this->translator->trans('ekyna_commerce.checkout.payment.pay_with', [
                        '%method%' => $payment->getMethod()->getTitle(),
                    ]),
                    'translation_domain' => false,
                    'disabled'           => !empty($options['lock_message']),
                ]);
            }
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

        $view->vars['payment'] = $payment;
        $view->vars['lock_message'] = $options['lock_message'];
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
