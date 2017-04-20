<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\CommerceBundle\Form\EventListener\ShipmentMethodTypeSubscriber;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\MessagesType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\MethodTranslationType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\TaxGroupChoiceType;
use Ekyna\Bundle\MediaBundle\Form\Type\MediaChoiceType;
use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Component\Commerce\Shipment\Entity;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayRegistryInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class ShipmentMethodType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentMethodType extends AbstractResourceType
{
    private GatewayRegistryInterface $registry;


    public function __construct(GatewayRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, [
                'label' => t('field.name', [], 'EkynaUi'),
            ])
            ->add('platformName', ShipmentPlatformChoiceType::class, [
                'disabled' => true,
            ])
            ->add('taxGroup', TaxGroupChoiceType::class)
            ->add('media', MediaChoiceType::class, [
                'label' => t('field.image', [], 'EkynaUi'),
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
                'label'    => t('field.enabled', [], 'EkynaUi'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('available', Type\CheckboxType::class, [
                'label'    => t('field.front_office', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ]);

        $builder->addEventSubscriber(new ShipmentMethodTypeSubscriber($this->registry));
    }
}
