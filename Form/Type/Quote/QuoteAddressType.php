<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Quote;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\AddressType;

/**
 * Class QuoteAddressType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Quote
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteAddressType extends ResourceFormType
{
    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return AddressType::class;
    }
}
