<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Cart;

use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleItemType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CartItemType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Cart
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CartItemType extends SaleItemType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'item_type'            => static::class,
                'item_adjustment_type' => CartItemAdjustmentType::class,
            ]);
    }
}
