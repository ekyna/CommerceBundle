<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Component\Commerce\Supplier\Entity\SupplierTemplateTranslation;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class SupplierTemplateType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierTemplateType extends ResourceFormType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'ekyna_core.field.title',
            ])
            ->add('translations', TranslationsFormsType::class, [
                'form_type'      => SupplierTemplateTranslationType::class,
                'form_options'   => [
                    'data_class' => SupplierTemplateTranslation::class,
                ],
                'label'          => false,
                'error_bubbling' => false,
            ]);
    }
}
