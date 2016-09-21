<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Product;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Model\BundleChoiceRuleTypes;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class BundleChoiceRuleType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Product
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleChoiceRuleType extends ResourceFormType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', Type\ChoiceType::class, [
                'sizing' => 'sm',
                'label' => 'ekyna_core.field.type',
                'choices' => BundleChoiceRuleTypes::getChoices(),
                'attr' => [
                    'class' => 'no-select2',
                ]
            ])
            ->add('expression', Type\TextType::class, [
                'label' => 'ekyna_commerce.bundle_choice_rule.field.expression',
                'sizing' => 'sm',
            ])
            ->add('position', Type\HiddenType::class, [
                'attr' => [
                    'data-collection-role' => 'position',
                ],
            ]);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_bundle_choice_rule';
    }
}
