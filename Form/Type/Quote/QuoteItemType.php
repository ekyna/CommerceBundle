<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Quote;

use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleItemType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class QuoteItemType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Quote
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class QuoteItemType extends SaleItemType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'item_type'            => static::class,
                'item_adjustment_type' => QuoteItemAdjustmentType::class,
            ]);
    }
}
