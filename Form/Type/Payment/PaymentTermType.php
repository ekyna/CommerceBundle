<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Payment;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Model\PaymentTermTriggers;
use Ekyna\Component\Commerce\Payment\Entity\PaymentTermTranslation;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class PaymentTermType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Payment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentTermType extends ResourceFormType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', Type\TextType::class, [
                'label' => 'ekyna_core.field.name',
            ])
            ->add('days', Type\IntegerType::class, [
                'label' => 'ekyna_commerce.payment_term.field.days',
                'attr'  => [
                    'min' => 0,
                ],
            ])
            ->add('endOfMonth', Type\CheckboxType::class, [
                'label'    => 'ekyna_commerce.payment_term.field.end_of_month',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('trigger', Type\ChoiceType::class, [
                'label'   => 'ekyna_commerce.payment_term.field.trigger',
                'choices' => PaymentTermTriggers::getChoices(),
            ])
            ->add('translations', TranslationsFormsType::class, [
                'form_type'      => PaymentTermTranslationType::class,
                'form_options'   => [
                    'data_class' => PaymentTermTranslation::class,
                ],
                'label'          => false,
                'error_bubbling' => false,
            ]);
    }
}
