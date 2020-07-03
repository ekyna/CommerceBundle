<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Customer;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\IdentityType;
use Ekyna\Bundle\CommerceBundle\Model\NotificationTypes as BTypes;
use Ekyna\Bundle\CoreBundle\Form\Type\PhoneNumberType;
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
            ->add('title', Type\TextType::class, [
                'label'    => 'ekyna_core.field.title',
                'required' => false,
            ])
            ->add('phone', PhoneNumberType::class, [
                'label'           => 'ekyna_core.field.phone',
                'required'        => false,
                'country_field'   => 'country',
                //'default_country' => $country, // TODO Get country from customer
                'attr'            => [
                    'class' => 'address-phone',
                ],
            ])
            ->add('notifications', ConstantChoiceType::class, [
                'label'    => 'ekyna_commerce.notification.label.plural',
                'class'    => BTypes::class,
                'filter'   => [CTypes::MANUAL],
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ]);

        if (!$options['admin_mode']) {
            return;
        }

        $builder->add('description', Type\TextareaType::class, [
            'label'    => 'ekyna_commerce.field.description',
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
