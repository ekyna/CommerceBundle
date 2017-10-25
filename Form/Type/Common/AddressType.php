<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Common;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CountryChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\IdentityType;
use libphonenumber\PhoneNumberFormat;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AddressType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AddressType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['company']) {
            $builder->add('company', Type\TextType::class, [
                'label'    => 'ekyna_core.field.company',
                'required' => $options['company_required'],
                // todo constraint if required ?
                'attr'     => [
                    'class' => 'address-company',
                ],
            ]);
        }

        if ($options['identity']) {
            $builder->add('identity', IdentityType::class, [
                'required' => $options['identity_required'],
                'attr'     => [
                    'class' => 'address-identity',
                ],
            ]);
        }

        $builder
            ->add('street', Type\TextType::class, [
                'label'    => 'ekyna_core.field.street',
                'required' => $options['required'],
                'attr'     => [
                    'class' => 'address-street',
                ],
            ])
            ->add('complement', Type\TextType::class, [
                'label'    => 'ekyna_commerce.address.field.complement',
                'required' => false,
                'attr'     => [
                    'class' => 'address-complement',
                ],
            ])
            ->add('supplement', Type\TextType::class, [
                'label'    => 'ekyna_commerce.address.field.supplement',
                'required' => false,
                'attr'     => [
                    'class' => 'address-supplement',
                ],
            ])
            ->add('city', Type\TextType::class, [
                'label'    => 'ekyna_core.field.city',
                'required' => $options['required'],
                'attr'     => [
                    'class' => 'address-city',
                ],
            ])
            ->add('postalCode', Type\TextType::class, [
                'label'    => 'ekyna_core.field.postal_code',
                'required' => $options['required'],
                'attr'     => [
                    'class' => 'address-postal-code',
                ],
            ])
            ->add('country', CountryChoiceType::class, [
                'required' => $options['required'],
                'attr'     => [
                    'class' => 'address-country',
                ],
                'select2' => $options['select2']
            ])
            /*->add('state', Type\TextType::class, [
                'label'    => 'ekyna_core.field.company',
                'required' => false,
                'sizing' => 'sm',
                'attr' => [
                    'class' => 'address-state',
                ]
            ])*/
        ;

        if ($options['phones']) {
            $builder
                ->add('phone', PhoneNumberType::class, [
                    'label'          => 'ekyna_core.field.phone',
                    'required'       => $options['phone_required'],
                    'default_region' => 'FR', // TODO get user locale
                    'format'         => PhoneNumberFormat::NATIONAL,
                    'attr'           => [
                        'class' => 'address-phone',
                    ],
                ])
                ->add('mobile', PhoneNumberType::class, [
                    'label'          => 'ekyna_core.field.mobile',
                    'required'       => $options['mobile_required'],
                    'default_region' => 'FR', // TODO get user locale
                    'format'         => PhoneNumberFormat::NATIONAL,
                    'attr'           => [
                        'class' => 'address-mobile',
                    ],
                ]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'company'           => true,
                'identity'          => true,
                'country'           => true,
                'phones'            => true,
                'company_required'  => false,
                'identity_required' => false,
                'phone_required'    => false,
                'mobile_required'   => false,
                'select2'           => true,
            ])
            ->setAllowedTypes('company', 'bool')
            ->setAllowedTypes('identity', 'bool')
            ->setAllowedTypes('country', 'bool')
            ->setAllowedTypes('phones', 'bool')
            ->setAllowedTypes('company_required', 'bool')
            ->setAllowedTypes('identity_required', 'bool')
            ->setAllowedTypes('phone_required', 'bool')
            ->setAllowedTypes('mobile_required', 'bool')
            ->setAllowedTypes('select2', 'bool');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_address';
    }
}
