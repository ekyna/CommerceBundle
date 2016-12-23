<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Account;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use Ekyna\Bundle\CommerceBundle\Form\Type\CustomerAddressType;
use Ekyna\Bundle\CommerceBundle\Form\Type\IdentityType;
use libphonenumber\PhoneNumberFormat;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RegistrationType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RegistrationType extends AbstractType
{
    /**
     * @var string
     */
    private $customerClass;


    /**
     * Constructor.
     *
     * @param string $customerClass
     */
    public function __construct($customerClass)
    {
        $this->customerClass = $customerClass;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', Type\RepeatedType::class, [
                'type'            => Type\EmailType::class,
                'property_path' => 'user.email',
                'first_options'   => [
                    'label' => 'ekyna_core.field.email',
                ],
                'second_options'  => [
                    'label' => 'ekyna_core.field.verify',
                ],
                'invalid_message' => 'ekyna_commerce.account.email.mismatch',
            ])
            ->add('plainPassword', Type\RepeatedType::class, [
                'type'            => Type\PasswordType::class,
                'property_path'   => 'user.plainPassword',
                'first_options'   => [
                    'label' => 'ekyna_core.field.password',
                ],
                'second_options'  => [
                    'label' => 'ekyna_core.field.verify',
                ],
                'invalid_message' => 'fos_user.password.mismatch',
            ])
            ->add('company', Type\TextType::class, [
                'label'    => 'ekyna_core.field.company',
                'required' => false,
            ])
            ->add('identity', IdentityType::class)
            ->add('phone', PhoneNumberType::class, [
                'label'          => 'ekyna_core.field.phone',
                'required'       => false,
                'default_region' => 'FR', // TODO get user locale
                'format'         => PhoneNumberFormat::NATIONAL,
            ])
            ->add('mobile', PhoneNumberType::class, [
                'label'          => 'ekyna_core.field.mobile',
                'required'       => false,
                'default_region' => 'FR', // TODO get user locale
                'format'         => PhoneNumberFormat::NATIONAL,
            ])
            ->add('invoice', CustomerAddressType::class, [
                'label'         => false,
                'property_path' => 'addresses[0]',
            ])
            ->add('actions', FormActionsType::class, [
                'buttons' => [
                    'save' => [
                        'type'    => Type\SubmitType::class,
                        'options' => [
                            'button_class' => 'primary',
                            'label'        => 'ekyna_core.button.save',
                            'attr'         => ['icon' => 'ok'],
                        ],
                    ],
                    /*'cancel' => [
                        'type'    => Type\ButtonType::class,
                        'options' => [
                            'label'        => 'ekyna_core.button.cancel',
                            'button_class' => 'default',
                            'as_link'      => true,
                            'attr'         => [
                                'class' => 'form-cancel-btn',
                                'icon'  => 'remove',
                                'href'  => $cancelPath,
                            ],
                        ],
                    ],*/
                ],
            ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) {
            /** @var \Ekyna\Bundle\CommerceBundle\Model\CustomerInterface $customer */
            $customer = $event->getData();

            // Copy user email into customer email
            $customer->setEmail($customer->getUser()->getEmail());
        }, 2048);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'        => $this->customerClass,
                'csrf_token_id'     => 'registration',
                'validation_groups' => ['Registration'],
            ]);
    }
}
