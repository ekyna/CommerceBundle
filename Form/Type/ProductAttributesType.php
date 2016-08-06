<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type;

use Ekyna\Bundle\CommerceBundle\Form\DataTransformer\ProductAttributesTransformer;
use Ekyna\Component\Commerce\Product\Model\AttributeSetInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProductAttributeType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductAttributesType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var AttributeSetInterface $attributeSet */
        $attributeSet = $options['attribute_set'];

        $builder->addModelTransformer(new ProductAttributesTransformer($attributeSet));

        foreach ($attributeSet->getSlots() as $slot) {
            $group = $slot->getGroup();

            $builder->add('slot_' . $slot->getId(), ChoiceType::class, [
                'label' => $group->getName(),
                'choices' => $group->getAttributes()->toArray(),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'multiple' => $slot->isMultiple(),
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('error_bubbling', false)
            ->setRequired('attribute_set')
            ->setAllowedTypes('attribute_set', AttributeSetInterface::class);
    }
}
