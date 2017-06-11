<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Notification;

use Ekyna\Bundle\CommerceBundle\Model\Recipient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RecipientType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Notification
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RecipientType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label'  => false,
                'attr' => [
                    'placeholder' => 'ekyna_core.field.email',
                ]
            ])
            ->add('name', TextType::class, [
                'label'    => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'ekyna_core.field.name',
                ]
            ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Recipient::class,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_recipient';
    }
}
