<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Customer;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Component\Commerce\Customer\Entity\CustomerGroupTranslation;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class CustomerGroupType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Customer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerGroupType extends ResourceFormType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', Type\TextType::class, [
                'label'    => 'ekyna_core.field.name',
                'required' => false,
            ])
            ->add('business', Type\CheckboxType::class, [
                'label'    => 'ekyna_commerce.customer_group.field.business',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('registration', Type\CheckboxType::class, [
                'label'    => 'ekyna_commerce.customer_group.field.registration',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('default', Type\CheckboxType::class, [
                'label'    => 'ekyna_core.field.default',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('translations', TranslationsFormsType::class, [
                'form_type'      => CustomerGroupTranslationType::class,
                'form_options'   => [
                    'data_class' => CustomerGroupTranslation::class,
                ],
                'label'          => false,
                'error_bubbling' => false,
            ]);
    }
}
