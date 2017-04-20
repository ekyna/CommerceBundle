<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Order;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\AddressType;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;

/**
 * Class OrderAddressType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Order
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderAddressType extends AbstractResourceType
{
    public function getParent(): ?string
    {
        return AddressType::class;
    }
}
