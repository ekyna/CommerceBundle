<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Customer;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CountryChoiceType;
use Ekyna\Bundle\CommerceBundle\Model\AddressImportLabel;
use Ekyna\Bundle\UiBundle\Form\Type\KeyValueCollectionType;
use Ekyna\Component\Commerce\Customer\Import\AddressImport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotNull;

use function Symfony\Component\Translation\t;

/**
 * Class AddressImportType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Customer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AddressImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', Type\FileType::class, [
                'label'       => t('field.file', [], 'EkynaUi'),
                'mapped'      => false,
                'constraints' => [
                    new NotNull(),
                    new File([
                        'maxSize'          => '1024k',
                        'mimeTypes'        => [
                            'text/plain',
                            'text/csv',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid CSV file',
                    ]),
                ],
            ])
            ->add('columns', KeyValueCollectionType::class, [
                'key_options'  => [
                    'choice_label' => function ($choice, $key) {
                        return AddressImportLabel::getLabel($key);
                    },
                ],
                'value_type'   => Type\IntegerType::class,
                'allowed_keys' => AddressImport::getColumnKeys(),
            ])
            ->add('from', Type\IntegerType::class, [
                'label'    => t('customer_address.field.import_from', [], 'EkynaCommerce'),
                'required' => false,
            ])
            ->add('to', Type\IntegerType::class, [
                'label'    => t('customer_address.field.import_to', [], 'EkynaCommerce'),
                'required' => false,
            ])
            ->add('separator', Type\TextType::class, [
                'label' => t('field.separator', [], 'EkynaUi'),
            ])
            ->add('enclosure', Type\TextType::class, [
                'label' => t('field.separator', [], 'EkynaUi'),
            ])
            ->add('defaultCountry', CountryChoiceType::class, [
                'label' => t('customer_address.field.default_country', [], 'EkynaCommerce'),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AddressImport::class,
        ]);
    }
}
