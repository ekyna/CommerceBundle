<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Quote;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\AddressType;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;

/**
 * Class QuoteAddressType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Quote
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteAddressType extends AbstractResourceType
{
    public function getParent(): ?string
    {
        return AddressType::class;
    }
}
