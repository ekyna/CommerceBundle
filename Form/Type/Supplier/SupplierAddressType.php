<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\AddressType;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;

/**
 * Class SupplierAddressType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierAddressType extends AbstractResourceType
{
    public function getParent(): ?string
    {
        return AddressType::class;
    }
}
