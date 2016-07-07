<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Ekyna\Bundle\UserBundle\Form\Type\IdentityType;
use libphonenumber\PhoneNumberFormat;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AbstractAddressType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AbstractAddressType extends ResourceFormType
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
        $builder
            ->add('company', Type\TextType::class, [
                'label'    => 'ekyna_core.field.company',
                'required' => false,
                'sizing'   => 'sm',
            ])
            ->add('identity', IdentityType::class)
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
            ])*/
            ->add('phone', PhoneNumberType::class, [
                'label'          => 'ekyna_core.field.phone',
                'required'       => false,
                'default_region' => 'FR', // TODO get user locale
                'format'         => PhoneNumberFormat::NATIONAL,
                'sizing'         => 'sm',
            ])
            ->add('mobile', PhoneNumberType::class, [
                'label'          => 'ekyna_core.field.mobile',
                'required'       => false,
                'default_region' => 'FR', // TODO get user locale
                'format'         => PhoneNumberFormat::NATIONAL,
                'sizing'         => 'sm',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_address';
    }
}
