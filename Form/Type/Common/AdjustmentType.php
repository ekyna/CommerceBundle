<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Common;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Model\AdjustmentModes;
use Ekyna\Bundle\CommerceBundle\Model\AdjustmentTypes;
use Ekyna\Component\Commerce\Common\Model\AdjustmentModes as AM;
use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes as AT;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AdjustmentType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AdjustmentType extends ResourceFormType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('designation', Type\TextType::class, [
                'label'  => 'ekyna_core.field.designation',
                'sizing' => 'sm',
                'attr'   => [
                    'placeholder' => 'ekyna_core.field.designation',
                ],
            ])
            ->add('type', Type\ChoiceType::class, [
                'label'             => 'ekyna_core.field.type',
                'choices'           => AdjustmentTypes::getChoices($options['types'], AdjustmentTypes::FILTER_RESTRICT),
                'preferred_choices' => [AT::TYPE_DISCOUNT],
                'sizing'            => 'sm',
                'select2'           => false,
                'attr'              => [
                    'placeholder' => 'ekyna_core.field.type',
                ],
            ])
            ->add('mode', Type\ChoiceType::class, [
                'label'             => 'ekyna_core.field.mode',
                'choices'           => AdjustmentModes::getChoices($options['modes'], AdjustmentTypes::FILTER_RESTRICT),
                'preferred_choices' => [AM::MODE_PERCENT],
                'sizing'            => 'sm',
                'select2'           => false,
                'attr'              => [
                    'placeholder' => 'ekyna_core.field.mode',
                ],
            ])
            ->add('amount', Type\NumberType::class, [
                'label'  => 'ekyna_core.field.value',
                'scale'  => 2,
                'sizing' => 'sm',
                'attr'   => [
                    'placeholder' => 'ekyna_core.field.value',
                ],
            ])
            ->add('position', Type\HiddenType::class, [
                'attr' => [
                    'data-collection-role' => 'position',
                ],
            ]);
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
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

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_adjustment';
    }
}
