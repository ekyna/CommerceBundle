<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\EventListener\ShipmentMethodTypeSubscriber;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\MessagesType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\MethodTranslationType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\TaxGroupChoiceType;
use Ekyna\Bundle\MediaBundle\Form\Type\MediaChoiceType;
use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Ekyna\Component\Commerce\Shipment\Entity;
use Ekyna\Component\Commerce\Shipment\Gateway\RegistryInterface;
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
     * @var RegistryInterface
     */
    private $registry;


    /**
     * Constructor.
     *
     * @param string            $class
     * @param RegistryInterface $registry
     */
    public function __construct($class, RegistryInterface $registry)
    {
        parent::__construct($class);

        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', Type\TextType::class, [
                'label' => 'ekyna_core.field.name',
            ])
            ->add('platformName', ShipmentPlatformChoiceType::class, [
                'disabled' => true,
            ])
            ->add('taxGroup', TaxGroupChoiceType::class)
            ->add('media', MediaChoiceType::class, [
                'label' => 'ekyna_core.field.image',
                'types' => MediaTypes::IMAGE,
            ])
            ->add('translations', TranslationsFormsType::class, [
                'form_type'    => MethodTranslationType::class,
                'form_options' => [
                    'data_class' => Entity\ShipmentMethodTranslation::class,
                ],
                'label'        => false,
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
                'label'    => 'ekyna_commerce.field.front_office',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ]);

        $builder->addEventSubscriber(new ShipmentMethodTypeSubscriber($this->registry));
    }
}
