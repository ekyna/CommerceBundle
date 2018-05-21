<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Common;

use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
use Ekyna\Bundle\GoogleBundle\Form\Type\CoordinateType;
use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
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
        $section = $options['section'] ? 'section-' . $options['section'] . ' ' : '';

        if ($options['company']) {
            $builder->add('company', Type\TextType::class, [
                'label'    => 'ekyna_core.field.company',
                'required' => $options['company_required'],
                // todo constraint if required ?
                'attr'     => [
                    'class'        => 'address-company',
                    'placeholder'  => 'ekyna_commerce.address.help.company',
                    'maxlength'    => 35,
                    'autocomplete' => $section . 'organization',
                ],
            ]);
        }

        if ($options['identity']) {
            $builder->add('identity', IdentityType::class, [
                'required' => $options['identity_required'],
                'section'  => $options['section'],
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
                    'class'        => 'address-street',
                    'placeholder'  => 'ekyna_commerce.address.help.street',
                    'maxlength'    => 35,
                    'autocomplete' => $section . 'address-line1',
                ],
            ])
            ->add('complement', Type\TextType::class, [
                'label'    => 'ekyna_commerce.address.field.complement',
                'required' => false,
                'attr'     => [
                    'class'        => 'address-complement',
                    'placeholder'  => 'ekyna_commerce.address.help.complement',
                    'maxlength'    => 35,
                    'autocomplete' => $section . 'address-line2',
                ],
            ])
            ->add('supplement', Type\TextType::class, [
                'label'    => 'ekyna_commerce.address.field.supplement',
                'required' => false,
                'attr'     => [
                    'class'        => 'address-supplement',
                    'placeholder'  => 'ekyna_commerce.address.help.supplement',
                    'maxlength'    => 35,
                    'autocomplete' => $section . 'address-line3',
                ],
            ])
            ->add('extra', Type\TextType::class, [
                'label'    => 'ekyna_commerce.address.field.extra',
                'required' => false,
                'attr'     => [
                    'class'       => 'address-supplement',
                    'placeholder' => 'ekyna_commerce.address.help.extra',
                    'maxlength'   => 35,
                ],
            ])
            ->add('city', Type\TextType::class, [
                'label'    => 'ekyna_core.field.city',
                'required' => $options['required'],
                'attr'     => [
                    'class'        => 'address-city',
                    'placeholder'  => 'ekyna_core.field.city',
                    'maxlength'    => 35,
                    'autocomplete' => $section . 'address-level2',
                ],
            ])
            ->add('postalCode', Type\TextType::class, [
                'label'    => 'ekyna_core.field.postal_code',
                'required' => $options['required'],
                'attr'     => [
                    'class'        => 'address-postal-code',
                    'placeholder'  => 'ekyna_core.field.postal_code',
                    'maxlength'    => 10,
                    'autocomplete' => $section . 'postal-code',
                ],
            ])
            ->add('country', CountryChoiceType::class, [
                'required' => $options['required'],
                'select2'  => $options['select2'],
                'attr'     => [
                    'class'        => 'address-country',
                    'autocomplete' => $section . 'country',
                ],
            ])
            ->add('digicode1', Type\TextType::class, [
                'label'    => 'ekyna_commerce.address.field.digicode1',
                'required' => false,
                'attr'     => [
                    'class'        => 'address-digicode1',
                    'placeholder'  => 'ekyna_commerce.address.field.digicode1',
                    'maxlength'    => 8,
                    'autocomplete' => 'digicode1', // Non standard to suppress warning
                ],
            ])
            ->add('digicode2', Type\TextType::class, [
                'label'    => 'ekyna_commerce.address.field.digicode2',
                'required' => false,
                'attr'     => [
                    'class'        => 'address-digicode2',
                    'placeholder'  => 'ekyna_commerce.address.field.digicode2',
                    'maxlength'    => 8,
                    'autocomplete' => 'digicode2', // Non standard to suppress warning
                ],
            ])
            ->add('intercom', Type\TextType::class, [
                'label'    => 'ekyna_commerce.address.field.intercom',
                'required' => false,
                'attr'     => [
                    'class'        => 'address-intercom',
                    'placeholder'  => 'ekyna_commerce.address.field.intercom',
                    'maxlength'    => 10,
                    'autocomplete' => 'intercomid', // Non standard to suppress warning
                ],
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

            $phonesListener = function (FormEvent $event) use ($options, $section) {
                $data = $event->getData();
                $address = $event->getForm()->getNormData();
                if ($address && !$address instanceof AddressInterface) {
                    throw new InvalidArgumentException("Expected instance of " . AddressInterface::class);
                }

                $region = PhoneNumberUtil::UNKNOWN_REGION;
                if ($data && $data instanceof AddressInterface && null !== $country = $address->getCountry()) {
                    $region = $country->getCode();
                } elseif (is_array($data) && isset($data['country'])) {
                    $region = $data['country'];
                } elseif ($address && null !== $country = $address->getCountry()) {
                    $region = $country->getCode();
                }

                $form = $event->getForm();

                $form
                    ->remove('phone')
                    ->remove('mobile')
                    ->add('phone', PhoneNumberType::class, [
                        'label'          => 'ekyna_core.field.phone',
                        'required'       => $options['phone_required'],
                        'default_region' => $region,
                        'format'         => PhoneNumberFormat::NATIONAL,
                        'attr'           => [
                            'class'        => 'address-phone',
                            'placeholder'  => 'ekyna_core.field.phone',
                            'autocomplete' => $section . 'tel-national',
                        ],
                    ])
                    ->add('mobile', PhoneNumberType::class, [
                        'label'          => 'ekyna_core.field.mobile',
                        'required'       => $options['mobile_required'],
                        'default_region' => $region,
                        'format'         => PhoneNumberFormat::NATIONAL,
                        'attr'           => [
                            'class'        => 'address-mobile',
                            'placeholder'  => 'ekyna_core.field.mobile',
                            'autocomplete' => $section . 'tel-national',
                        ],
                    ]);
            };

            $builder
                ->addEventListener(FormEvents::POST_SET_DATA, $phonesListener)
                ->addEventListener(FormEvents::PRE_SUBMIT, $phonesListener);
        }

        if ($options['coordinate']) {
            $builder->add('coordinate', CoordinateType::class, array_replace([
                'map_height' => 260,
            ], $options['coordinate_options']));
        }
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        FormUtil::addClass($view, 'address');
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'company'            => true,
                'identity'           => true,
                'country'            => true,
                'phones'             => true,
                'coordinate'         => true,
                'company_required'   => false,
                'identity_required'  => false,
                'phone_required'     => false,
                'mobile_required'    => false,
                'coordinate_options' => [],
                'select2'            => true,
                'section'            => 'address',
            ])
            ->setAllowedTypes('company', 'bool')
            ->setAllowedTypes('identity', 'bool')
            ->setAllowedTypes('country', 'bool')
            ->setAllowedTypes('phones', 'bool')
            ->setAllowedTypes('coordinate', 'bool')
            ->setAllowedTypes('company_required', 'bool')
            ->setAllowedTypes('identity_required', 'bool')
            ->setAllowedTypes('phone_required', 'bool')
            ->setAllowedTypes('mobile_required', 'bool')
            ->setAllowedTypes('coordinate_options', 'array')
            ->setAllowedTypes('select2', 'bool')
            ->setAllowedTypes('section', 'string');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_address';
    }
}
