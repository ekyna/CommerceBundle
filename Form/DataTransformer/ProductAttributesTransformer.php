<?php

namespace Ekyna\Bundle\CommerceBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\CommerceBundle\Model\ProductAttributesSlot;
use Ekyna\Component\Commerce\Product\Model\AttributeSetInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class ProductAttributesTransformer
 * @package Ekyna\Bundle\CommerceBundle\Form\DataTransformer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductAttributesTransformer implements DataTransformerInterface
{
    /**
     * @var AttributeSetInterface
     */
    private $attributeSet;


    /**
     * Constructor.
     *
     * @param AttributeSetInterface $attributeSet
     */
    public function __construct(AttributeSetInterface $attributeSet)
    {
        $this->attributeSet = $attributeSet;
    }

    /**
     * @inheritDoc
     *
     * Transforms a collection of attributes to an array of slots.
     */
    public function transform($attributes)
    {
        if ($attributes instanceof Collection) {
            $attributes = $attributes->toArray();
        } else {
            $attributes = [];
        }

        $slots = [];

        foreach ($this->attributeSet->getSlots() as $slot) {
            $slotGroup = $slot->getGroup();
            $slotAttributes = [];

            foreach ($attributes as $attribute) {
                if ($attribute->getGroup() === $slotGroup) {
                    if (!$slot->isMultiple()) {
                        $slotAttributes = $attribute;
                        break;
                    }

                    $slotAttributes[$attribute->getId()] = $attribute;
                }
            }

            if (is_array($slotAttributes) && empty($slotAttributes)) {
                $slotAttributes = null;
            }

            $slots['slot_' . $slot->getId()] = $slotAttributes;
        }

        return $slots;
    }

    /**
     * @inheritDoc
     *
     * Transforms an array of slots to a collection of attributes.
     */
    public function reverseTransform($slots)
    {
        if (!is_array($slots)) {
            throw new TransformationFailedException('Expected array');
        }

        $attributes = new ArrayCollection();

        /** @var ProductAttributesSlot $slot */
        foreach ($slots as $slotId => $slotAttributes) {
            if (is_array($slotAttributes)) {
                foreach ($slotAttributes as $attribute) {
                    if (!$attributes->contains($attribute)) {
                        $attributes->add($attribute);
                    }
                }
            } elseif(null !== $slotAttributes) {
                if (!$attributes->contains($slotAttributes)) {
                    $attributes->add($slotAttributes);
                }
            }
        }

        return $attributes;
    }
}
