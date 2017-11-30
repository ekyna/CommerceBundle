<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Order;

use Ekyna\Bundle\CmsBundle\Form\Type\TagChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleType;
use Ekyna\Bundle\UserBundle\Form\Type\UserChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OrderType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Order
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderType extends SaleType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('sample', CheckboxType::class, [
                'label'    => 'ekyna_commerce.field.sample',
                'required' => false,
                'attr' => [
                    'align_with_widget' => true,
                ]
            ])
            ->add('inCharge', UserChoiceType::class, [
                'label'    => 'ekyna_commerce.customer.field.in_charge',
                'required' => false,
                'roles'    => ['ROLE_ADMIN'],
            ])
            ->add('tags', TagChoiceType::class, [
                'required' => false,
                'multiple' => true,
            ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('address_type', OrderAddressType::class);
    }
}
