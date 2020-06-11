<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Common;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\CommerceBundle\Model\DocumentTypes;
use Ekyna\Bundle\CoreBundle\Form\Type\CollectionPositionType;
use Ekyna\Bundle\ResourceBundle\Form\Type\ConstantChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MentionType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class MentionType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('documentTypes', ConstantChoiceType::class, [
                'label'    => 'ekyna_commerce.document.label.plural',
                'class'    => DocumentTypes::class,
                'multiple' => true,
                'required' => false,
            ])
            ->add('translations', TranslationsFormsType::class, [
                'form_type'      => $options['translation_type'],
                'form_options'   => [
                    'data_class' => $options['translation_class'],
                ],
                'label'          => false,
                'error_bubbling' => false,
                'attr'           => [
                    'label_col'  => 0,
                    'widget_col' => 12,
                ],
            ])
            ->add('position', CollectionPositionType::class);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('translation_class')
            ->setDefault('translation_type', MentionTranslationType::class)
            ->setAllowedTypes('translation_class', 'string')
            ->setAllowedTypes('translation_type', 'string');
    }
}
