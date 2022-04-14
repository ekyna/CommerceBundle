<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Customer\Import;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CountryChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\GenderChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerSearchType;
use Ekyna\Bundle\ResourceBundle\Form\Type\Import\AbstractConfigType;
use Ekyna\Component\Commerce\Customer\Import\AddressConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

use function Symfony\Component\Translation\t;

/**
 * Class AddressConfigType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Customer\Import
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AddressConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('defaultGender', GenderChoiceType::class, [
                'label'       => t('import.default_gender', [], 'EkynaCommerce'),
                'required'    => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('defaultCountry', CountryChoiceType::class, [
                'label'       => t('import.default_country', [], 'EkynaCommerce'),
                'required'    => true,
                'constraints' => [
                    new NotNull(),
                ],
            ]);

        if ($options['customer']) {
            $builder->add('customer', CustomerSearchType::class, [
                'label'    => t('customer.label.singular', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label'      => 'Address config',
                'data_class' => AddressConfig::class,
                'customer'   => false,
            ])
            ->setAllowedTypes('customer', 'bool');
    }

    public function getParent(): ?string
    {
        return AbstractConfigType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_import_address_config';
    }
}
