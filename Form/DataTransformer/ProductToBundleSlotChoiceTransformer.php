<?php

namespace Ekyna\Bundle\CommerceBundle\Form\DataTransformer;

use Ekyna\Component\Commerce\Product\Model\BundleChoiceInterface;
use Ekyna\Component\Commerce\Product\Model\BundleSlotInterface;
use Ekyna\Component\Commerce\Product\Model\ProductInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class ProductToBundleSlotChoiceTransformer
 * @package Ekyna\Bundle\CommerceBundle\Form\DataTransformer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductToBundleSlotChoiceTransformer implements DataTransformerInterface
{
    /**
     * @var BundleSlotInterface
     */
    private $bundleSlot;


    /**
     * Constructor.
     *
     * @param BundleSlotInterface $bundleSlot
     */
    public function __construct(BundleSlotInterface $bundleSlot)
    {
        $this->bundleSlot = $bundleSlot;
    }

    /**
     * Transforms a product to a bundle slot choice.
     * @inheritdoc
     */
    public function transform($value)
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof ProductInterface) {
            throw new TransformationFailedException("Expected ProductInterface");
        }

        foreach ($this->bundleSlot->getChoices() as $choice) {
            if ($choice->getProduct() === $value) {
                return $choice;
            }
        }

        throw new TransformationFailedException("Failed to resolve bundle slot choice.");
    }

    /**
     * Transforms a bundle slot choice to a product.
     * @inheritdoc
     */
    public function reverseTransform($value)
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof BundleChoiceInterface) {
            return $value->getProduct();
        }

        throw new TransformationFailedException("Expected BundleChoiceInterface.");
    }
}
