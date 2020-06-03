<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Customer;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\IdentityType;
use Ekyna\Bundle\CommerceBundle\Model\NotificationTypes as BTypes;
use Ekyna\Component\Commerce\Common\Model\NotificationTypes as CTypes;
use Ekyna\Bundle\ResourceBundle\Form\Type\ConstantChoiceType;
use Ekyna\Component\Commerce\Customer\Entity\CustomerContact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CustomerContactType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Customer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerContactType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('identity', IdentityType::class, [
                'section'  => 'billing',
            ])
            ->add('email', Type\TextType::class, [
                'label'    => 'ekyna_core.field.email',
            ])
            ->add('phone', Type\TextType::class, [
                'label'    => 'ekyna_core.field.phone',
                'required' => false,
            ])
            ->add('notifications', ConstantChoiceType::class, [
                'label'    => 'ekyna_commerce.notification.label.plural',
                'class'    => BTypes::class,
                'filter'   => [CTypes::MANUAL],
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', CustomerContact::class);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_customer_contact';
    }
}
