<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Order;

use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleItemType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OrderItemType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Order
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderItemType extends SaleItemType
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
                'item_adjustment_type' => OrderItemAdjustmentType::class,
            ]);
    }
}
