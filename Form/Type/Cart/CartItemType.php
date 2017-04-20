<?php

declare(strict_types=1);

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
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'item_type'            => static::class,
                'item_adjustment_type' => CartItemAdjustmentType::class,
            ]);
    }
}
