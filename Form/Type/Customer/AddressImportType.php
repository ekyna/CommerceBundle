<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Customer;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CountryChoiceType;
use Ekyna\Bundle\CommerceBundle\Model\AddressImportLabel;
use Ekyna\Bundle\CoreBundle\Form\Type\KeyValueCollectionType;
use Ekyna\Component\Commerce\Customer\Import\AddressImport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * Class AddressImportType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Customer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AddressImportType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', Type\FileType::class, [
                'label'       => 'ekyna_core.field.file',
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
                    'choice_label' => function ($choice, $key, $value) {
                        return AddressImportLabel::getLabel($key);
                    },
                ],
                'value_type'   => Type\IntegerType::class,
                'allowed_keys' => AddressImport::getColumnKeys(),
            ])
            ->add('from', Type\IntegerType::class, [
                'label'    => 'ekyna_commerce.customer_address.field.import_from',
                'required' => false,
            ])
            ->add('to', Type\IntegerType::class, [
                'label'    => 'ekyna_commerce.customer_address.field.import_to',
                'required' => false,
            ])
            ->add('separator', Type\TextType::class, [
                'label' => 'ekyna_core.field.separator',
            ])
            ->add('enclosure', Type\TextType::class, [
                'label' => 'ekyna_core.field.separator',
            ])
            ->add('defaultCountry', CountryChoiceType::class, [
                'label' => 'ekyna_commerce.customer_address.field.default_country',
            ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AddressImport::class,
        ]);
    }
}
