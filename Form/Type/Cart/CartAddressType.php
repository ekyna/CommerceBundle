<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Cart;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\AddressType;

/**
 * Class CartAddressType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Cart
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartAddressType extends ResourceFormType
{
    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return AddressType::class;
    }
}
