<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Common;

use Ekyna\Bundle\CommerceBundle\Model\AdjustmentModes;
use Ekyna\Bundle\CommerceBundle\Model\AdjustmentTypes;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\ConstantChoiceType;
use Ekyna\Bundle\ResourceBundle\Model\ConstantsInterface;
use Ekyna\Bundle\UiBundle\Form\Type\CollectionPositionType;
use Ekyna\Component\Commerce\Common\Model\AdjustmentModes as AM;
use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes as AT;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class AdjustmentType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AdjustmentType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('designation', Type\TextType::class, [
                'label'    => t('field.designation', [], 'EkynaUi'),
                'required' => false,
                'attr'     => [
                    'placeholder' => t('field.designation', [], 'EkynaUi'),
                ],
            ])
            ->add('type', ConstantChoiceType::class, [
                'label'             => t('field.type', [], 'EkynaUi'),
                'class'             => AdjustmentTypes::class,
                'filter'            => $options['types'],
                'filter_mode'       => ConstantsInterface::FILTER_RESTRICT,
                'preferred_choices' => [AT::TYPE_DISCOUNT],
                'select2'           => false,
                'attr'              => [
                    'placeholder' => t('field.type', [], 'EkynaUi'),
                ],
            ])
            ->add('mode', ConstantChoiceType::class, [
                'label'             => t('field.mode', [], 'EkynaUi'),
                'class'             => AdjustmentModes::class,
                'filter'            => $options['modes'],
                'filter_mode'       => ConstantsInterface::FILTER_RESTRICT,
                'preferred_choices' => [AM::MODE_PERCENT],
                'select2'           => false,
                'attr'              => [
                    'placeholder' => t('field.mode', [], 'EkynaUi'),
                ],
            ])
            ->add('amount', Type\NumberType::class, [
                'label'   => t('field.value', [], 'EkynaUi'),
                'decimal' => true,
                'scale'   => 5,
                'attr'    => [
                    'placeholder' => t('field.value', [], 'EkynaUi'),
                ],
            ])
            ->add('position', CollectionPositionType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'types' => [],
                'modes' => [],
            ])
            ->setAllowedTypes('types', 'array')
            ->setAllowedTypes('modes', 'array');
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_adjustment';
    }
}
