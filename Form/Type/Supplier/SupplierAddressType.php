<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\Type\AddressType;

/**
 * Class SupplierAddressType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierAddressType extends ResourceFormType
{
    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return AddressType::class;
    }
}
