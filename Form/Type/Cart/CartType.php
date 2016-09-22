<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Cart;

use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CartType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Cart
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartType extends SaleType
{
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('address_type', CartAddressType::class);
    }
}
