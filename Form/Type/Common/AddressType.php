<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Common;

use Ekyna\Bundle\GoogleBundle\Form\Type\CoordinateType;
use Ekyna\Bundle\UiBundle\Form\Type\PhoneNumberType;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use libphonenumber\PhoneNumberType as PhoneType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_replace;
use function Symfony\Component\Translation\t;

/**
 * Class AddressType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $section = $options['section'] ? 'section-' . $options['section'] . ' ' : '';

        if ($options['company']) {
            $builder->add('company', Type\TextType::class, [
                'label'    => t('address.field.company', [], 'EkynaCommerce'),
                'required' => $options['company_required'],
                // todo constraint if required ?
                'attr'     => [
                    'class'        => 'address-company',
                    'placeholder'  => t('address.field.company', [], 'EkynaCommerce'),
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
                'label'    => t('address.field.street', [], 'EkynaCommerce'),
                'required' => $options['required'],
                'attr'     => [
                    'class'        => 'address-street',
                    'placeholder'  => t('address.field.street', [], 'EkynaCommerce'),
                    'maxlength'    => 35,
                    'autocomplete' => $section . 'address-line1',
                ],
            ])
            ->add('complement', Type\TextType::class, [
                'label'    => t('address.field.complement', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'class'        => 'address-complement',
                    'placeholder'  => t('address.field.complement', [], 'EkynaCommerce'),
                    'maxlength'    => 35,
                    'autocomplete' => $section . 'address-line2',
                ],
            ])
            ->add('supplement', Type\TextType::class, [
                'label'    => t('address.field.supplement', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'class'        => 'address-supplement',
                    'placeholder'  => t('address.field.supplement', [], 'EkynaCommerce'),
                    'maxlength'    => 35,
                    'autocomplete' => $section . 'address-line3',
                ],
            ])
            ->add('extra', Type\TextType::class, [
                'label'    => t('address.field.extra', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'class'       => 'address-supplement',
                    'placeholder' => t('address.field.extra', [], 'EkynaCommerce'),
                    'maxlength'   => 35,
                ],
            ])
            ->add('city', Type\TextType::class, [
                'label'    => t('field.city', [], 'EkynaUi'),
                'required' => $options['required'],
                'attr'     => [
                    'class'        => 'address-city',
                    'placeholder'  => t('field.city', [], 'EkynaUi'),
                    'maxlength'    => 35,
                    'autocomplete' => $section . 'address-level2',
                ],
            ])
            ->add('postalCode', Type\TextType::class, [
                'label'    => t('field.postal_code', [], 'EkynaUi'),
                'required' => $options['required'],
                'attr'     => [
                    'class'        => 'address-postal-code',
                    'placeholder'  => t('field.postal_code', [], 'EkynaUi'),
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
                'label'    => t('address.field.digicode1', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'class'        => 'address-digicode1',
                    'placeholder'  => t('address.field.digicode1', [], 'EkynaCommerce'),
                    'maxlength'    => 8,
                    'autocomplete' => 'digicode1', // Non standard to suppress warning
                ],
            ])
            ->add('digicode2', Type\TextType::class, [
                'label'    => t('address.field.digicode2', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'class'        => 'address-digicode2',
                    'placeholder'  => t('address.field.digicode2', [], 'EkynaCommerce'),
                    'maxlength'    => 8,
                    'autocomplete' => 'digicode2', // Non standard to suppress warning
                ],
            ])
            ->add('intercom', Type\TextType::class, [
                'label'    => t('address.field.intercom', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'class'        => 'address-intercom',
                    'placeholder'  => t('address.field.intercom', [], 'EkynaCommerce'),
                    'maxlength'    => 10,
                    'autocomplete' => 'intercomid', // Non standard to suppress warning
                ],
            ]);
        /*->add('state', Type\TextType::class, [
            'label'    => t('field.company', [], 'EkynaUi'),
            'required' => false,
            'sizing' => 'sm',
            'attr' => [
                'class' => 'address-state',
            ]
        ])*/

        if ($options['phones']) {
            $builder
                ->add('phone', PhoneNumberType::class, [
                    'label'         => t('field.phone', [], 'EkynaUi'),
                    'required'      => $options['phone_required'],
                    'country_field' => 'country',
                    'attr'          => [
                        'class' => 'address-phone',
                    ],
                    'number_attr'   => [
                        'autocomplete' => $section . 'tel-national',
                    ],
                ])
                ->add('mobile', PhoneNumberType::class, [
                    'label'         => t('field.mobile', [], 'EkynaUi'),
                    'required'      => $options['mobile_required'],
                    'type'          => PhoneType::MOBILE,
                    'country_field' => 'country',
                    'attr'          => [
                        'class' => 'address-mobile',
                    ],
                    'number_attr'   => [
                        'autocomplete' => $section . 'tel-national',
                    ],
                ]);
        }

        if ($options['coordinate']) {
            $builder->add('coordinate', CoordinateType::class, array_replace([
                'map_height' => 260,
            ], $options['coordinate_options']));
        }
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        FormUtil::addClass($view, 'address');
    }

    public function configureOptions(OptionsResolver $resolver): void
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

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_address';
    }
}
