<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use libphonenumber\PhoneNumberFormat;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AddressType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AddressType extends ResourceFormType
{
    /**
     * @var string
     */
    private $countryClass;


    /**
     * Constructor.
     *
     * @param string $addressClass
     * @param string $countryClass
     */
    public function __construct($addressClass, $countryClass)
    {
        parent::__construct($addressClass);

        $this->countryClass = $countryClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['company']) {
            $builder->add('company', Type\TextType::class, [
                'label'    => 'ekyna_core.field.company',
                'required' => $options['company_required'],
            ]);
        }

        if ($options['identity']) {
            $builder->add('identity', IdentityType::class, [
                'required' => $options['identity_required'],
            ]);
        }

        $builder
            ->add('street', Type\TextType::class, [
                'label'    => 'ekyna_core.field.street',
                'required' => false,
                'sizing'   => 'sm',
            ])
            ->add('supplement', Type\TextType::class, [
                'label'    => 'ekyna_core.field.supplement',
                'required' => false,
                'sizing'   => 'sm',
            ])
            ->add('city', Type\TextType::class, [
                'label'    => 'ekyna_core.field.city',
                'required' => false,
                'sizing'   => 'sm',
            ])
            ->add('postalCode', Type\TextType::class, [
                'label'    => 'ekyna_core.field.postal_code',
                'required' => false,
                'sizing'   => 'sm',
            ])
            ->add('country', CountryChoiceType::class, [
                'sizing'   => 'sm',
                'required' => $options['required'],
            ])
            /*->add('state', Type\TextType::class, [
                'label'    => 'ekyna_core.field.company',
                'required' => false,
                'sizing' => 'sm',
            ])*/;

        if ($options['phones']) {
            $builder
                ->add('phone', PhoneNumberType::class, [
                    'label'          => 'ekyna_core.field.phone',
                    'required'       => $options['phone_required'],
                    'default_region' => 'FR', // TODO get user locale
                    'format'         => PhoneNumberFormat::NATIONAL,
                ])
                ->add('mobile', PhoneNumberType::class, [
                    'label'          => 'ekyna_core.field.mobile',
                    'required'       => $options['mobile_required'],
                    'default_region' => 'FR', // TODO get user locale
                    'format'         => PhoneNumberFormat::NATIONAL,
                ]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'company'           => true,
            'identity'          => true,
            'country'           => true,
            'phones'            => true,
            'company_required'  => false,
            'identity_required' => true,
            'phone_required'    => false,
            'mobile_required'   => false,
        ]);

        $resolver->setAllowedTypes('company', 'bool');
        $resolver->setAllowedTypes('identity', 'bool');
        $resolver->setAllowedTypes('country', 'bool');
        $resolver->setAllowedTypes('phones', 'bool');
        $resolver->setAllowedTypes('company_required', 'bool');
        $resolver->setAllowedTypes('identity_required', 'bool');
        $resolver->setAllowedTypes('phone_required', 'bool');
        $resolver->setAllowedTypes('mobile_required', 'bool');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_address';
    }
}
