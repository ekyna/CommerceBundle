<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Order;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\AddressType;

/**
 * Class OrderAddressType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Order
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderAddressType extends ResourceFormType
{
    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return AddressType::class;
    }
}
