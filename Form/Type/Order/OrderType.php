<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Order;

use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OrderType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Order
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderType extends SaleType
{
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('address_type', OrderAddressType::class);
    }
}
