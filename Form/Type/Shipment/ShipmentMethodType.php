<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\Type\MessagesType;
use Ekyna\Bundle\CommerceBundle\Form\Type\MethodTranslationType;
use Ekyna\Bundle\MediaBundle\Form\Type\MediaChoiceType;
use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Ekyna\Component\Commerce\Shipment\Entity;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class ShipmentMethodType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentMethodType extends ResourceFormType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', Type\TextType::class, [
                'label' => 'ekyna_core.field.name',
            ])
            ->add('media', MediaChoiceType::class, [
                'label' => 'ekyna_core.field.image',
                'types' => MediaTypes::IMAGE,
            ])
            ->add('translations', TranslationsFormsType::class, [
                'form_type'      => MethodTranslationType::class,
                'form_options'   => [
                    'data_class' => Entity\ShipmentMethodTranslation::class,
                ],
                'label'          => false,
                'error_bubbling' => false,
            ])
            ->add('messages', MessagesType::class, [
                'message_class'     => Entity\ShipmentMessage::class,
                'translation_class' => Entity\ShipmentMessageTranslation::class,
            ])
            ->add('enabled', Type\CheckboxType::class, [
                'label'    => 'ekyna_core.field.enabled',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('available', Type\CheckboxType::class, [
                'label'    => 'ekyna_commerce.shipment_method.field.available',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ]);
    }
}
