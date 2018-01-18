<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Customer;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Component\Commerce\Customer\Entity\CustomerGroupTranslation;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

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
            ->add('quoteAllowed', Type\CheckboxType::class, [
                'label'    => 'ekyna_commerce.customer_group.field.quote_allowed',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('freeShipping', Type\CheckboxType::class, [
                'label'    => 'ekyna_commerce.customer_group.field.free_shipping',
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

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface $group */
            $group = $event->getData();
            $form = $event->getForm();

            $form->add('default', Type\CheckboxType::class, [
                'label'    => 'ekyna_core.field.default',
                'required' => false,
                'disabled' => $group->isDefault(),
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ]);
        });
    }
}
