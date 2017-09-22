<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Customer;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
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
            ]);
    }
}
