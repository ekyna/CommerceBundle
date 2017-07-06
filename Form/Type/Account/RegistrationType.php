<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Account;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerAddressType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\IdentityType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\VatNumberType;
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
     * @var array
     */
    private $config;


    /**
     * Constructor.
     *
     * @param string $customerClass
     * @param array  $config
     */
    public function __construct($customerClass, array $config)
    {
        $this->customerClass = $customerClass;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', Type\RepeatedType::class, [
                'type'            => Type\EmailType::class,
                'property_path'   => 'user.email',
                'first_options'   => [
                    'label' => 'ekyna_core.field.email',
                ],
                'second_options'  => [
                    'label' => 'ekyna_commerce.account.registration.field.email_confirm',
                ],
                'invalid_message' => 'ekyna_commerce.account.email.mismatch',
            ])
            ->add('plainPassword', Type\RepeatedType::class, [
                'type'            => Type\PasswordType::class,
                'property_path'   => 'user.plainPassword',
                'first_options'   => [
                    'label'        => 'ekyna_core.field.password',
                    'always_empty' => false,
                ],
                'second_options'  => [
                    'label'        => 'ekyna_commerce.account.registration.field.password_confirm',
                    'always_empty' => false,
                ],
                'invalid_message' => 'fos_user.password.mismatch',
            ])
            ->add('company', Type\TextType::class, [
                'label'    => 'ekyna_core.field.company',
                'required' => false,
            ])
            ->add('vatNumber', VatNumberType::class)
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
                'identity'      => false,
                'company'       => false,
                'phones'        => false,
                'defaults'      => false,
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
                ],
            ]);

        if ($this->config['birthday']) {
            $builder->add('birthday', Type\DateTimeType::class, [
                'label'    => 'ekyna_core.field.birthday',
                'required' => false,
                'format'   => 'dd/MM/yyyy', // TODO localized format
            ]);
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var \Ekyna\Bundle\CommerceBundle\Model\CustomerInterface $customer */
            $customer = $event->getData();
            $user = $customer->getUser();

            // Copy user email to username
            $email = $user->getEmail();
            $user->setUsername($email);

            // Copy user email into customer email
            $customer->setEmail($email);

        }, 2048);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'        => $this->customerClass,
            'csrf_token_id'     => 'registration',
            'validation_groups' => ['Registration'],
        ]);
    }
}
