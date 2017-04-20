<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Customer;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\IdentityType;
use Ekyna\Bundle\CommerceBundle\Model\NotificationTypes as BNotifications;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\ConstantChoiceType;
use Ekyna\Bundle\UiBundle\Form\Type\PhoneNumberType;
use Ekyna\Component\Commerce\Common\Model\NotificationTypes as CNotification;
use Ekyna\Component\Commerce\Customer\Entity\CustomerContact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class CustomerContactType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Customer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerContactType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('identity', IdentityType::class, [
                'section' => 'billing',
            ])
            ->add('email', Type\TextType::class, [
                'label' => t('field.email', [], 'EkynaUi'),
            ])
            ->add('title', Type\TextType::class, [
                'label'    => t('field.title', [], 'EkynaUi'),
                'required' => false,
            ])
            ->add('phone', PhoneNumberType::class, [
                'label'         => t('field.phone', [], 'EkynaUi'),
                'required'      => false,
                'country_field' => 'country',
                //'default_country' => $country, // TODO Get country from customer
                'attr'          => [
                    'class' => 'address-phone',
                ],
            ])
            ->add('notifications', ConstantChoiceType::class, [
                'label'    => t('notification.label.plural', [], 'EkynaCommerce'),
                'class'    => BNotifications::class,
                'filter'   => [CNotification::MANUAL],
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ]);

        if (!$options['admin_mode']) {
            return;
        }

        $builder->add('description', Type\TextareaType::class, [
            'label'    => t('field.description', [], 'EkynaCommerce'),
            'required' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_customer_contact';
    }
}
