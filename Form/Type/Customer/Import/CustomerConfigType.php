<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Customer\Import;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CountryChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CurrencyChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\GenderChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerGroupChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerSearchType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Payment\PaymentMethodChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Payment\PaymentTermChoiceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\Import\AbstractConfigType;
use Ekyna\Bundle\ResourceBundle\Form\Type\LocaleChoiceType;
use Ekyna\Component\Commerce\Customer\Import\CustomerConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

use function Symfony\Component\Translation\t;

/**
 * Class CustomerConfigType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Customer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerConfigType extends AbstractType
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
            ->add('defaultLocale', LocaleChoiceType::class, [
                'label'       => t('import.default_locale', [], 'EkynaCommerce'),
                'required'    => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('defaultCurrency', CurrencyChoiceType::class, [
                'label'       => t('import.default_currency', [], 'EkynaCommerce'),
                'required'    => true,
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('defaultCountry', CountryChoiceType::class, [
                'label'       => t('import.default_country', [], 'EkynaCommerce'),
                'required'    => true,
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('defaultGroup', CustomerGroupChoiceType::class, [
                'label'       => t('customer.import.default_group', [], 'EkynaCommerce'),
                'required'    => true,
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('defaultParent', CustomerSearchType::class, [
                'label'    => t('customer.import.default_parent', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [],
            ])
            ->add('defaultPaymentMethod', PaymentMethodChoiceType::class, [
                'label'       => t('customer.field.default_payment_method', [], 'EkynaCommerce'),
                'required'    => false,
                'public'      => false,
                'credit'      => false,
                'outstanding' => false,
            ])
            ->add('defaultPaymentTerm', PaymentTermChoiceType::class, [
                'label'    => t('customer.import.default_payment_term', [], 'EkynaCommerce'),
                'required' => false,
            ])
            ->add('skipDuplicateEmail', CheckboxType::class, [
                'label'    => t('customer.import.skip_duplicate_email', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('label', 'Customer config') // TODO
            ->setDefault('data_class', CustomerConfig::class);
    }

    public function getParent(): ?string
    {
        return AbstractConfigType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_import_customer_config';
    }
}
